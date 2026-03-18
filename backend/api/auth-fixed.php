<?php
// Axio Auth API - Fixed demoMode signup rejection
@require_once __DIR__ . '/../config/auto-setup.php';
require_once __DIR__ . '/../config/database-fixed.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

class Auth {
    private $db;
    private $user;
    private $demoMode = false;

    public function __construct() {
        $this->db = new Database();
        $this->demoMode = $this->db->isDemoMode();
        if (!$this->demoMode) {
            $this->user = new User($this->db->getConnection());
        }
    }

    public function checkUsernameAvailable($username) {
        $username = trim($username);
        if (strlen($username) < 3) return json_encode(['success' => false, 'available' => false, 'message' => 'Username too short']);
        if ($this->demoMode) return json_encode(['success' => true, 'available' => true]);
        $exists = $this->user->findByUsername($username);
        return json_encode(['success' => true, 'available' => !$exists]);
    }

    public function register($username, $password) {
        $username = trim($username);
        $password = $password;

        if (strlen($username) < 3) return json_encode(['success' => false, 'message' => 'Username must be at least 3 chars']);
        if (strlen($password) < 6) return json_encode(['success' => false, 'message' => 'Password must be at least 6 chars']);

        // CRITICAL FIX: Reject signup in demoMode - expose real DB problem
        if ($this->demoMode) {
            return json_encode([
                'success' => false,
                'message' => 'Database unavailable - see RENDER_DATABASE_SECURITY_FIX.md. Use testuser/testpass123 for login',
                'demo_mode' => true,
                'error' => 'db_unavailable'
            ]);
        }

        $existingUser = $this->user->findByUsername($username);
        if ($existingUser) return json_encode(['success' => false, 'message' => 'Username exists']);

        $createSuccess = $this->user->create($username, $password);
        if ($createSuccess) {
            $newUser = $this->user->findByUsername($username);
            return json_encode([
                'success' => true,
                'message' => 'User registered successfully.',
                'user_id' => $newUser['id'],
                'username' => $newUser['username']
            ]);
        }
        return json_encode(['success' => false, 'message' => $this->user->getLastError() ?: 'Registration failed']);
    }

    public function login($username, $password) {
        $username = trim($username);
        $password = $password;

        if ($username === '' || $password === '') return json_encode(['success' => false, 'message' => 'Username/password required']);

        if ($this->demoMode) {
            if ($username === 'testuser' && $password === 'testpass123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'testuser';
                return json_encode(['success' => true, 'message' => 'Demo login OK', 'user_id' => 1, 'username' => 'testuser', 'demo_mode' => true]);
            }
            return json_encode(['success' => false, 'message' => 'DB unavailable. Try: testuser/testpass123', 'auth_error' => 'db_unavailable']);
        }
        
        $user = $this->user->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            return json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return json_encode(['success' => true, 'message' => 'Login OK', 'user_id' => $user['id'], 'username' => $user['username']]);
    }

    public function logout() {
        session_destroy();
        return json_encode(['success' => true, 'message' => 'Logged out']);
    }
}

// Route requests
try {
    $auth = new Auth();
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $data['action'] ?? $_GET['action'] ?? $_POST['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($action === 'check_session' || !$action)) {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id && !$auth->isDemoMode()) {
            $db = new Database();
            $stmt = $db->getConnection()->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $valid = $stmt->fetch();
            if ($valid) {
                echo json_encode(['success' => true, 'authenticated' => true, 'user_id' => $user_id]);
            } else {
                session_destroy();
                echo json_encode(['success' => true, 'authenticated' => false]);
            }
        } else {
            echo json_encode(['success' => true, 'authenticated' => false]);
        }
        exit;
    }

    switch ($action) {
        case 'register':
        case 'signup':
            echo $auth->register($data['username'] ?? '', $data['password'] ?? '');
            break;
        case 'check_username':
            echo $auth->checkUsernameAvailable($data['username'] ?? '');
            break;
        case 'login':
            echo $auth->login($data['username'] ?? '', $data['password'] ?? '');
            break;
        case 'logout':
            echo $auth->logout();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Auth service error', 'message' => $e->getMessage()]);
}
?>

