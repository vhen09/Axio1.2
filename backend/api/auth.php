<?php
// This file handles user authentication, including login and registration processes.

require_once '../config/database.php';
require_once '../models/User.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

    public function register($username, $password) {
        if ($this->demoMode) {
            return json_encode(['success' => true, 'message' => 'Demo mode: User registered successfully (not saved)', 'demo_mode' => true, 'user_id' => 1]);
        }
        
        if ($this->user->create($username, $password)) {
            return json_encode(['success' => true, 'message' => 'User registered successfully.']);
        } else {
            return json_encode(['success' => false, 'message' => 'Registration failed.']);
        }
    }

    public function login($username, $password) {
        if ($this->demoMode) {
            return json_encode(['success' => true, 'message' => 'Demo mode: Login successful', 'demo_mode' => true, 'user_id' => 1, 'username' => $username]);
        }
        
        $user = $this->user->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return json_encode(['success' => true, 'message' => 'Login successful.', 'user_id' => $user['id'], 'username' => $user['username']]);
        } else {
            return json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    }
}

// Example usage
$auth = new Auth();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? $_POST['action'] ?? '';
    
    if ($action === 'register') {
        $username = $data['username'] ?? $_POST['username'] ?? '';
        $password = $data['password'] ?? $_POST['password'] ?? '';
        echo $auth->register($username, $password);
    } elseif ($action === 'login') {
        $username = $data['username'] ?? $_POST['username'] ?? '';
        $password = $data['password'] ?? $_POST['password'] ?? '';
        echo $auth->login($username, $password);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>