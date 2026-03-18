<?php
// This file handles user authentication, including login and registration processes.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

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

    public function isDemoMode() {
        return $this->demoMode;
    }

    public function checkUsernameAvailable($username) {
        $username = trim((string)$username);

        if (strlen($username) < 3) {
            return json_encode(['success' => false, 'available' => false, 'message' => 'Username must be at least 3 characters']);
        }

        if ($this->demoMode) {
            return json_encode(['success' => true, 'available' => true]);
        }

        $exists = $this->user->findByUsername($username);
        return json_encode(['success' => true, 'available' => !$exists]);
    }

    public function register($username, $password) {
        $username = trim((string)$username);
        $password = (string)$password;

        if (strlen($username) < 3) {
            return json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        }

        if (strlen($password) < 6) {
            return json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        }

        if ($this->demoMode) {
            return json_encode(['success' => true, 'message' => 'Demo mode: User registered successfully (not saved)', 'demo_mode' => true, 'user_id' => 1, 'username' => $username]);
        }

        // Debug: Log registration attempt
        error_log("=== REGISTRATION ATTEMPT ===");
        error_log("Username: " . $username);
        error_log("Demo mode: " . ($this->demoMode ? 'YES' : 'NO'));
        
        $existingUser = $this->user->findByUsername($username);
        error_log("DB Lookup result: " . var_export($existingUser, true));
        
        if ($existingUser) {
            error_log("Username '$username' already exists in database");
            return json_encode(['success' => false, 'message' => 'Username already exists. Please choose another one.']);
        }
        
        error_log("Username '$username' is available, attempting creation...");
        
        if ($this->user->create($username, $password)) {
            $newUser = $this->user->findByUsername($username);
            error_log("Account created successfully for '$username'");
            return json_encode([
                'success' => true, 
                'message' => 'User registered successfully.',
                'user_id' => $newUser['id'],
                'username' => $newUser['username']
            ]);
        } else {
            $modelError = method_exists($this->user, 'getLastError') ? $this->user->getLastError() : null;
            return json_encode(['success' => false, 'message' => $modelError ?: 'Registration failed.']);
        }
    }

    public function login($username, $password) {
        $username = trim((string)$username);
        $password = (string)$password;

        if ($username === '' || $password === '') {
            return json_encode(['success' => false, 'message' => 'Username and password are required.']);
        }

        if ($this->demoMode) {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $username !== '' ? $username : 'demo_user';
            return json_encode(['success' => true, 'message' => 'Demo mode: Login successful', 'demo_mode' => true, 'user_id' => 1, 'username' => $_SESSION['username']]);
        }
        
        $user = $this->user->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return json_encode(['success' => true, 'message' => 'Login successful.', 'user_id' => $user['id'], 'username' => $user['username']]);
        } else {
            return json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        return json_encode(['success' => true, 'message' => 'Logout successful.']);
    }

    public function resetPassword($username, $newPassword) {
        $username = trim((string)$username);
        $newPassword = (string)$newPassword;

        if ($username === '') {
            return json_encode(['success' => false, 'message' => 'Username is required']);
        }

        if (strlen($newPassword) < 6) {
            return json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        }

        if ($this->demoMode) {
            return json_encode(['success' => true, 'message' => 'Password reset successful (demo mode)']);
        }

        $user = $this->user->findByUsername($username);
        if (!$user) {
            return json_encode(['success' => false, 'message' => 'Username not found']);
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->user->updatePassword($user['id'], $hashedPassword);
        
        return json_encode(['success' => true, 'message' => 'Password reset successful']);
    }
}

// Example usage
try {
    $auth = new Auth();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        if ($action === 'check_session' || $action === '') {
            echo json_encode([
                'success' => true,
                'authenticated' => isset($_SESSION['user_id']),
                'user_id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? null
            ]);
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }
        $action = $data['action'] ?? $_POST['action'] ?? '';
        
        if ($action === 'register' || $action === 'signup' || $action === 'sign_up') {
            $username = $data['username'] ?? $_POST['username'] ?? '';
            $password = $data['password'] ?? $_POST['password'] ?? '';
            echo $auth->register($username, $password);
        } elseif ($action === 'check_username') {
            $username = $data['username'] ?? $_POST['username'] ?? '';
            echo $auth->checkUsernameAvailable($username);
        } elseif ($action === 'login') {
            $username = $data['username'] ?? $_POST['username'] ?? '';
            $password = $data['password'] ?? $_POST['password'] ?? '';
            echo $auth->login($username, $password);
        } elseif ($action === 'logout') {
            echo $auth->logout();
        } elseif ($action === 'reset_password') {
            $username = $data['username'] ?? $_POST['username'] ?? '';
            $newPassword = $data['newPassword'] ?? $_POST['newPassword'] ?? '';
            echo $auth->resetPassword($username, $newPassword);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    // Database configuration error - return clear error message
    error_log('CRITICAL AUTH ERROR: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'System authentication service is unavailable. Please contact support.',
        'message' => 'Authentication service unreachable'
    ]);
}
?>