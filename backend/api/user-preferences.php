<?php
// User Preferences API - handles onboarding, tutorial state, skill level
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UserPreferences.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Require authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit();
    }

    $db = new Database();
    $conn = $db->getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    $userPrefs = new UserPreferences($conn);
    $user_id = (int)$_SESSION['user_id'];

    // Parse request
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $data = $_SERVER['REQUEST_METHOD'] === 'POST' 
        ? json_decode(file_get_contents('php://input'), true) ?? [] 
        : [];

    switch ($action) {
        case 'get_status':
            // Get user's onboarding/tutorial status
            $status = $userPrefs->getOnboardingStatus($user_id);
            if ($status) {
                echo json_encode(['success' => true, 'data' => $status]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not retrieve status']);
            }
            break;

        case 'set_latex_skill':
            // Set LaTeX skill level after assessment  
            $level = $data['level'] ?? $_POST['level'] ?? '';
            if (!in_array($level, ['beginner', 'intermediate', 'advanced'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid skill level']);
                break;
            }

            if ($userPrefs->setLatexSkillLevel($user_id, $level)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Skill level updated',
                    'level' => $level
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update skill level']);
            }
            break;

        case 'mark_tutorial_completed':
            // Mark tutorial as completed
            if ($userPrefs->markTutorialCompleted($user_id)) {
                echo json_encode(['success' => true, 'message' => 'Tutorial marked as completed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to mark tutorial']);
            }
            break;

        case 'skip_tutorial':
            // User says they're familiar with system - skip tutorial
            if ($userPrefs->markTutorialSkipped($user_id)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Tutorial skipped - redirecting to dashboard',
                    'tutorial_skipped' => true
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to skip tutorial']);
            }
            break;

        case 'should_show_tutorial':
            // Check if user should see tutorial on this session
            $status = $userPrefs->getOnboardingStatus($user_id);
            $should_show = $status && !($status['tutorial_completed'] || $status['tutorial_skipped']);
            
            echo json_encode([
                'success' => true,
                'should_show_tutorial' => (bool)$should_show,
                'status' => $status
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('User preferences error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
}
?>
