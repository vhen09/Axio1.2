<?php
/**
 * ActivityLog Model
 * Manages user access and action logging
 */

class ActivityLog {
    private $connection;
    private $table = 'activity_logs';

    public function __construct($connection) {
        $this->connection = $connection;
    }

    /**
     * Log a user activity
     * @param int $userId
     * @param string $action ('login', 'signup', 'logout', 'proof_submitted', 'theorem_viewed', etc.)
     * @param string $resourceType ('user', 'theorem', 'proof', 'settings', etc.)
     * @param int|null $resourceId
     * @param array $metadata Additional data (proof_id, score, etc.)
     * @param string|null $ipAddress
     * @return bool Success status
     */
    public function log($userId, $action, $resourceType, $resourceId = null, $metadata = [], $ipAddress = null) {
        try {
            // Get IP address if not provided
            if (!$ipAddress) {
                $ipAddress = $this->getClientIpAddress();
            }

            // Convert metadata array to JSON
            $metadataJson = json_encode($metadata);

            // Insert into activity_logs table
            $query = "INSERT INTO activity_logs (user_id, action, resource_type, resource_id, metadata, ip_address, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                error_log("ActivityLog: Failed to prepare statement - " . print_r($this->connection->errorInfo(), true));
                return false;
            }

            $result = $stmt->execute([
                $userId,
                $action,
                $resourceType,
                $resourceId,
                $metadataJson,
                $ipAddress
            ]);

            if ($result) {
                error_log("ActivityLog: Logged action '$action' for user $userId");
            } else {
                error_log("ActivityLog: Failed to execute statement");
            }

            return $result;

        } catch (Exception $e) {
            error_log("ActivityLog: Error logging activity - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all activities for a specific user
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @return array Activities or empty array
     */
    public function getUserActivities($userId, $limit = 50, $offset = 0) {
        try {
            $query = "SELECT id, user_id, action, resource_type, resource_id, metadata, ip_address, created_at 
                      FROM activity_logs 
                      WHERE user_id = ? 
                      ORDER BY created_at DESC 
                      LIMIT ? OFFSET ?";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId, $limit, $offset]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("ActivityLog: Error retrieving user activities - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get activities by action type
     * @param string $action
     * @param int $limit
     * @return array Activities or empty array
     */
    public function getActivitiesByAction($action, $limit = 100) {
        try {
            $query = "SELECT id, user_id, action, resource_type, resource_id, metadata, ip_address, created_at 
                      FROM activity_logs 
                      WHERE action = ? 
                      ORDER BY created_at DESC 
                      LIMIT ?";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$action, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("ActivityLog: Error retrieving activities by action - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get login history for a user
     * @param int $userId
     * @param int $limit
     * @return array Login activities
     */
    public function getLoginHistory($userId, $limit = 20) {
        try {
            $query = "SELECT id, action, ip_address, created_at, metadata 
                      FROM activity_logs 
                      WHERE user_id = ? AND action IN ('login', 'logout') 
                      ORDER BY created_at DESC 
                      LIMIT ?";
            
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("ActivityLog: Error retrieving login history - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all recent activities (admin view)
     * @param int $limit
     * @param string|null $action Filter by action
     * @return array Activities
     */
    public function getRecentActivities($limit = 100, $action = null) {
        try {
            if ($action) {
                $query = "SELECT id, user_id, action, resource_type, resource_id, metadata, ip_address, created_at 
                          FROM activity_logs 
                          WHERE action = ? 
                          ORDER BY created_at DESC 
                          LIMIT ?";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([$action, $limit]);
            } else {
                $query = "SELECT id, user_id, action, resource_type, resource_id, metadata, ip_address, created_at 
                          FROM activity_logs 
                          ORDER BY created_at DESC 
                          LIMIT ?";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([$limit]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("ActivityLog: Error retrieving recent activities - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user access statistics
     * @param int $userId
     * @return array Statistics
     */
    public function getUserStats($userId) {
        try {
            $stats = [];

            // Total logins
            $query = "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ? AND action = 'login'";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId]);
            $stats['total_logins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Last login
            $query = "SELECT created_at FROM activity_logs WHERE user_id = ? AND action = 'login' ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['last_login'] = $result ? $result['created_at'] : null;

            // Total activities
            $query = "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ?";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId]);
            $stats['total_activities'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Last activity
            $query = "SELECT created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['last_activity'] = $result ? $result['created_at'] : null;

            // Most used IP addresses
            $query = "SELECT ip_address, COUNT(*) as count FROM activity_logs WHERE user_id = ? GROUP BY ip_address ORDER BY count DESC LIMIT 5";
            $stmt = $this->connection->prepare($query);
            $stmt->execute([$userId]);
            $stats['ip_addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $stats;

        } catch (Exception $e) {
            error_log("ActivityLog: Error retrieving user stats - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client IP address
     * @return string IP address
     */
    private function getClientIpAddress() {
        // Check for IP from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IP passed from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Check for remote address
        else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        return $ip;
    }
}
?>
