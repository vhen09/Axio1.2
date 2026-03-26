<?php
/**
 * Activity Logs API
 * Provides access to user activity tracking and audit logs
 */

// Use API safety handler
require_once __DIR__ . '/../config/api-safety.php';

// Database and models
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ActivityLog.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Configure session for CORS - Use Lax for localhost HTTP compatibility
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', false); // Allow HTTP for localhost
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_path', '/');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Debug: Log session info
$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

error_log("=== Activity Logs API Called ===");
error_log("Session ID: " . $sessionId);
error_log("Session User ID: " . ($userId ?? "NOT SET"));
error_log("Session Username: " . ($username ?? "NOT SET"));
error_log("Session Data Keys: " . implode(", ", array_keys($_SESSION)));
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request Action: " . ($_GET['action'] ?? $_POST['action'] ?? "NOT SET"));
error_log("Cookies: " . print_r($_COOKIE, true));

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    if (!$connection) {
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection unavailable'
        ]);
        exit;
    }

    $activityLog = new ActivityLog($connection);
    
    // Determine action
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        if ($action === 'my_activities') {
            // Get current user's activities
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                error_log("Activity Log Access Denied - No session user_id");
                http_response_code(401);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Not authenticated',
                    'message' => 'Please log in first',
                    'debug' => [
                        'session_id' => session_id(),
                        'session_user_id' => $_SESSION['user_id'] ?? null,
                        'session_username' => $_SESSION['username'] ?? null
                    ]
                ]);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $limit = intval($_GET['limit'] ?? 50);
            $offset = intval($_GET['offset'] ?? 0);

            error_log("Loading activities for user $userId (limit: $limit, offset: $offset)");
            $activities = $activityLog->getUserActivities($userId, $limit, $offset);
            
            echo json_encode([
                'success' => true,
                'user_id' => $userId,
                'activities' => $activities,
                'count' => count($activities)
            ]);

        } elseif ($action === 'login_history') {
            // Get login history for current user
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                error_log("Login History Access Denied - No session user_id");
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Not authenticated', 'message' => 'Please log in first']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $limit = intval($_GET['limit'] ?? 20);

            error_log("Loading login history for user $userId");
            $history = $activityLog->getLoginHistory($userId, $limit);
            
            echo json_encode([
                'success' => true,
                'user_id' => $userId,
                'login_history' => $history,
                'count' => count($history)
            ]);

        } elseif ($action === 'user_stats') {
            // Get access statistics for current user
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                error_log("User Stats Access Denied - No session user_id");
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Not authenticated', 'message' => 'Please log in first']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            error_log("Loading stats for user $userId");
            $stats = $activityLog->getUserStats($userId);
            
            echo json_encode([
                'success' => true,
                'user_id' => $userId,
                'statistics' => $stats
            ]);

        } elseif ($action === 'recent_activities') {
            // Get recent activities (admin only - optional verification)
            $limit = $_GET['limit'] ?? 100;
            $actionFilter = $_GET['action_filter'] ?? null;

            $activities = $activityLog->getRecentActivities($limit, $actionFilter);
            
            echo json_encode([
                'success' => true,
                'activities' => $activities,
                'count' => count($activities)
            ]);

        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'available_actions' => [
                    'my_activities' => 'Get current user\'s activities',
                    'login_history' => 'Get login history',
                    'user_stats' => 'Get user access statistics',
                    'recent_activities' => 'Get recent activities (admin)'
                ]
            ]);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Manual logging endpoint (for testing)
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if ($action === 'log_action') {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Not authenticated']);
                exit;
            }

            $userId = $_SESSION['user_id'];
            $actionType = $data['action'] ?? '';
            $resourceType = $data['resource_type'] ?? 'activity';
            $resourceId = $data['resource_id'] ?? null;
            $metadata = $data['metadata'] ?? [];

            if (!$actionType) {
                echo json_encode(['success' => false, 'error' => 'Action is required']);
                exit;
            }

            $result = $activityLog->log($userId, $actionType, $resourceType, $resourceId, $metadata);
            
            echo json_encode([
                'success' => $result,
                'message' => $result ? 'Activity logged' : 'Failed to log activity',
                'action' => $actionType,
                'user_id' => $userId
            ]);

        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid POST action']);
        }

    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    }

} catch (Exception $e) {
    error_log('Activity Logs API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'System error',
        'message' => 'Failed to process activity logs'
    ]);
}
?>
