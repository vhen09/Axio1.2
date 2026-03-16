<?php
// This file handles user preferences and settings

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class UserPreferences {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function setLatexSkill($userId, $latexSkill) {
        // For now, store in session and localStorage on client
        // In a full implementation, you'd store this in the database
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['success' => false, 'message' => 'Not authenticated']);
        }

        $_SESSION['latex_skill'] = $latexSkill;
        $_SESSION['latex_skill_checked'] = true;

        return json_encode(['success' => true, 'message' => 'Preference saved', 'user_id' => $_SESSION['user_id'], 'username' => $_SESSION['username']]);
    }

    public function setTutorialCompleted($userId) {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['success' => false, 'message' => 'Not authenticated']);
        }

        $_SESSION['tutorial_completed'] = true;

        return json_encode(['success' => true, 'message' => 'Tutorial completion recorded']);
    }

    public function getLatexSkill($userId) {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['success' => false, 'message' => 'Not authenticated']);
        }

        $skill = $_SESSION['latex_skill'] ?? 'no';
        return json_encode(['success' => true, 'latex_skill' => $skill]);
    }

    public function setPreference($key, $value) {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['success' => false, 'message' => 'Not authenticated']);
        }

        $_SESSION['prefs'][$key] = $value;

        return json_encode(['success' => true, 'message' => 'Preference saved']);
    }

    public function getPreference($key) {
        if (!isset($_SESSION['user_id'])) {
            return json_encode(['success' => false, 'message' => 'Not authenticated']);
        }

        $value = $_SESSION['prefs'][$key] ?? null;
        return json_encode(['success' => true, 'value' => $value]);
    }
}

// Handle requests
$prefs = new UserPreferences();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? 'set_preference';

    switch ($action) {
        case 'set_latex_skill':
            echo $prefs->setLatexSkill($_SESSION['user_id'] ?? null, $data['latex_skill'] ?? 'no');
            break;

        case 'set_tutorial_completed':
            echo $prefs->setTutorialCompleted($_SESSION['user_id'] ?? null);
            break;

        case 'set_preference':
            echo $prefs->setPreference($data['key'] ?? null, $data['value'] ?? null);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'get_preference';
    $key = $_GET['key'] ?? null;

    switch ($action) {
        case 'get_latex_skill':
            echo $prefs->getLatexSkill($_SESSION['user_id'] ?? null);
            break;

        case 'get_preference':
            echo $prefs->getPreference($key);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
