<?php
// This file handles user authentication, including login and registration processes.

// Auto-initialize database fallback on every request
@require_once __DIR__ . '/../config/auto-setup.php';
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

    public function register($username, $password, $firstName = '', $lastName = '', $email = '') {
        $username = trim((string)$username);
        $password = (string)$password;
        $firstName = trim((string)$firstName);
        $lastName = trim((string)$lastName);
        $email = trim((string)$email);

        if (strlen($username) < 3) {
            return json_encode(['success' => false, 'message' => 'Username must be at least 3 characters']);
        }

        if (strlen($password) < 6) {
            return json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        }

        // CRITICAL: Reject signup if database unavailable
        if ($this->demoMode) {
            error_log("CRITICAL: Signup rejected - DATABASE UNAVAILABLE");
            error_log("Demo mode is ACTIVE - cannot create users");
            return json_encode([
                'success' => false,
                'message' => 'Database is currently unavailable. Please try again later.',
                'demo_mode' => true,
                'error_code' => 'db_unavailable'
            ]);
        }

        // Comprehensive logging for signup debugging
        error_log("==== SIGNUP REQUEST ====");
        error_log("Username: '$username'");
        error_log("First Name: '$firstName'");
        error_log("Last Name: '$lastName'");
        error_log("Email: '$email'");
        error_log("Production mode: " . (!$this->demoMode ? 'YES' : 'NO'));
        
        error_log("Checking username availability...");
        $existingUser = $this->user->findByUsername($username);
        
        if ($existingUser) {
            error_log("ERROR: Username '$username' already exists");
            return json_encode(['success' => false, 'message' => 'Username already exists. Please choose another one.']);
        }
        
        error_log("Username available - creating user...");
        $createSuccess = $this->user->create($username, $password, $firstName, $lastName, $email);
        
        if ($createSuccess) {
            $newUser = $this->user->findByUsername($username);
            if ($newUser) {
                error_log("SUCCESS: User created - ID: " . $newUser['id'] . ", Username: '$username'");
                error_log("New user should complete LaTeX tutorial on next login");
                
                // Set session for new user
                $_SESSION['user_id'] = $newUser['id'];
                $_SESSION['username'] = $newUser['username'];
                
                return json_encode([
                    'success' => true, 
                    'message' => 'User registered successfully.',
                    'user_id' => $newUser['id'],
                    'username' => $newUser['username'],
                    'first_name' => $newUser['first_name'] ?? $firstName,
                    'last_name' => $newUser['last_name'] ?? $lastName,
                    'email' => $newUser['email'] ?? $email,
                    'is_new_user' => true,
                    'redirect_to_tutorial' => true
                ]);
            } else {
                error_log("ERROR: Create succeeded but user not found in DB");
                return json_encode(['success' => false, 'message' => 'User created but verification failed.']);
            }
        } else {
            $modelError = method_exists($this->user, 'getLastError') ? $this->user->getLastError() : 'Unknown error';
            error_log("ERROR: User creation failed - " . $modelError);
            return json_encode(['success' => false, 'message' => $modelError ?: 'Registration failed.']);
        }
    }

    public function login($username, $password) {
        $username = trim((string)$username);
        $password = (string)$password;

        error_log("=== LOGIN ATTEMPT ===");
        error_log("Username: $username");
        error_log("Demo mode: " . ($this->demoMode ? 'YES' : 'NO'));

        if ($username === '' || $password === '') {
            error_log("LOGIN FAILED: Empty username or password");
            return json_encode(['success' => false, 'message' => 'Username and password are required.']);
        }

        // SECURITY: Login REQUIRES database connection. Never accept unverified credentials.
        if ($this->demoMode) {
            error_log("SECURITY ALERT: Login attempt in demo mode (database unavailable). REJECTING.");
            // In demo mode, allow login with test credentials for development
            if ($username === 'testuser' && $password === 'testpass123') {
                error_log("DEMO MODE: Allowing test user login");
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'testuser';
                return json_encode([
                    'success' => true,
                    'message' => 'Login successful (demo mode).', 
                    'user_id' => 1, 
                    'username' => 'testuser',
                    'demo_mode' => true
                ]);
            }
            return json_encode([
                'success' => false, 
                'message' => 'Database unavailable. For testing, use: testuser / testpass123',
                'auth_error' => 'database_unavailable'
            ]);
        }
        
        $user = $this->user->findByUsername($username);
        error_log("User lookup result: " . ($user ? 'FOUND' : 'NOT FOUND'));
        
        if (!$user) {
            error_log("LOGIN FAILED: User '$username' does not exist");
            return json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
        
        // Verify password using bcrypt hashing
        if (!password_verify($password, $user['password'])) {
            error_log("LOGIN FAILED: Invalid password for user '$username'");
            return json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
        
        // Password verified successfully
        error_log("LOGIN SUCCESS: User '$username' (ID: {$user['id']}) authenticated");
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return json_encode([
            'success' => true, 
            'message' => 'Login successful.', 
            'user_id' => $user['id'], 
            'username' => $user['username'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'email' => $user['email'] ?? ''
        ]);
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
            $authenticated = false;
            $user_id = null;
            $username = null;
            
            // Check if session has user_id
            if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                // SECURITY: Verify user still exists and credentials are valid
                if (!$this->demoMode) {
                    // Fetch user from database to validate session
                    try {
                        $db = new Database();
                        $connection = $db->getConnection();
                        $stmt = $connection->prepare("SELECT id, username FROM users WHERE id = ? LIMIT 1");
                        $stmt->execute([$_SESSION['user_id']]);
                        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($dbUser) {
                            // User still exists in database
                            $authenticated = true;
                            $user_id = $dbUser['id'];
                            $username = $dbUser['username'];
                        } else {
                            // User doesn't exist in DB - session is invalid
                            error_log("SECURITY: Session validation FAILED - user ID {$_SESSION['user_id']} not found in database");
                            session_destroy();
                            $_SESSION = [];
                        }
                    } catch (Exception $e) {
                        error_log("Session validation error: " . $e->getMessage());
                        $authenticated = false;
                    }
                } else {
                    // Cannot validate in demo mode
                    $authenticated = false;
                    error_log("SECURITY: Session check in demo mode - session validation disabled");
                }
            }
            
            echo json_encode([
                'success' => true,
                'authenticated' => $authenticated,
                'user_id' => $user_id,
                'username' => $username
            ]);
            exit();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            $data = [];
        }
        // Check action from URL query param, POST body, or $_POST
        $action = $_GET['action'] ?? $data['action'] ?? $_POST['action'] ?? '';
        
        if ($action === 'register' || $action === 'signup' || $action === 'sign_up') {
            $username = $data['username'] ?? $_POST['username'] ?? '';
            $password = $data['password'] ?? $_POST['password'] ?? '';
            $firstName = $data['first_name'] ?? $_POST['first_name'] ?? '';
            $lastName = $data['last_name'] ?? $_POST['last_name'] ?? '';
            $email = $data['email'] ?? $_POST['email'] ?? '';
            echo $auth->register($username, $password, $firstName, $lastName, $email);
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