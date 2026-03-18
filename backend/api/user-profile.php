<?php
/**
 * User Profile API - Get and update current user profile
 * Requires authentication (session must be valid)
 */

// Auto-initialize database fallback on every request
@require_once __DIR__ . '/../config/auto-setup.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // ✅ CRITICAL: Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated',
            'message' => 'Please log in to view your profile'
        ]);
        exit;
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? 'get';
    $user_id = $_SESSION['user_id'];

    switch ($action) {
        case 'get':
            // Get current user profile
            $db = new Database();
            
            if ($db->isDemoMode()) {
                // Demo mode: return basic info from session
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user_id,
                        'username' => $_SESSION['username'] ?? 'demouser',
                        'email' => 'demo@example.com',
                        'full_name' => $_SESSION['username'] ?? 'Demo User',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
                exit;
            }

            $stmt = $db->getConnection()->prepare(
                "SELECT id, username, email, full_name, created_at FROM users WHERE id = ?"
            );
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception('User not found in database');
            }

            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
            break;

        case 'update':
            // Update user profile
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                throw new Exception('No data provided');
            }

            $db = new Database();
            
            if ($db->isDemoMode()) {
                // Demo mode: just update session
                if (isset($data['username'])) {
                    $_SESSION['username'] = $data['username'];
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated (demo mode)',
                    'user' => [
                        'id' => $user_id,
                        'username' => $_SESSION['username'] ?? 'demouser',
                        'email' => 'demo@example.com'
                    ]
                ]);
                exit;
            }

            $updateFields = [];
            $updateValues = [];

            if (isset($data['email'])) {
                $updateFields[] = 'email = ?';
                $updateValues[] = $data['email'];
            }
            if (isset($data['full_name'])) {
                $updateFields[] = 'full_name = ?';
                $updateValues[] = $data['full_name'];
            }
            if (isset($data['username'])) {
                // Check if username is already taken
                $checkStmt = $db->getConnection()->prepare(
                    "SELECT id FROM users WHERE username = ? AND id != ?"
                );
                $checkStmt->execute([$data['username'], $user_id]);
                if ($checkStmt->fetch()) {
                    throw new Exception('Username already taken');
                }
                $updateFields[] = 'username = ?';
                $updateValues[] = $data['username'];
            }

            if (empty($updateFields)) {
                throw new Exception('Nothing to update');
            }

            $updateValues[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $db->getConnection()->prepare($sql);
            $stmt->execute($updateValues);

            // Fetch updated user
            $stmt = $db->getConnection()->prepare(
                "SELECT id, username, email, full_name FROM users WHERE id = ?"
            );
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Update session
            $_SESSION['username'] = $user['username'];

            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
            break;

        default:
            throw new Exception('Invalid action: ' . $action);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
