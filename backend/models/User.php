<?php

class User {
    private $id;
    private $username;
    private $password;
    private $email;
    private $connection;
    private $lastError = '';

    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public function getLastError() {
        return $this->lastError;
    }

    public function validatePassword($password) {
        if (!isset($this->password)) {
            return false;
        }
        return password_verify($password, $this->password);
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
    
    /**
     * Create a new user in the database
     */
    public function create($username, $password) {
        try {
            $username = trim((string)$username);
            $password = (string)$password;
            
            if (strlen($username) < 3) {
                $this->lastError = 'Username must be at least 3 characters';
                return false;
            }
            
            if (strlen($password) < 6) {
                $this->lastError = 'Password must be at least 6 characters';
                return false;
            }
            
            // Check if username already exists
            if ($this->findByUsername($username)) {
                $this->lastError = 'Username already exists';
                return false;
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                $errorInfo = $this->connection->errorInfo();
                $this->lastError = 'Database error: ' . $errorInfo[2];
                return false;
            }
            
            if ($stmt->execute([$username, $hashedPassword])) {
                $this->id = $this->connection->lastInsertId();
                $this->username = $username;
                
                // Create default user preferences for new user
                try {
                    $prefQuery = "INSERT INTO user_preferences (user_id) VALUES (?)";
                    $prefStmt = $this->connection->prepare($prefQuery);
                    if ($prefStmt) {
                        $prefStmt->execute([$this->id]);
                    }
                } catch (Exception $e) {
                    // Preferences creation failed but user was created, log it but continue
                    error_log('Failed to create user preferences for user ' . $this->id . ': ' . $e->getMessage());
                }
                
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                // Check for UNIQUE constraint violation (error code 23000)
                if ($errorInfo[0] == '23000') {
                    $this->lastError = 'Username already exists. Please choose another one.';
                } else {
                    $this->lastError = 'Failed to create user: ' . $errorInfo[2];
                }
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = 'Exception: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Find user by username
     */
    public function findByUsername($username) {
        try {
            $username = trim((string)$username);
            
            error_log("=== findByUsername START === Name: '$username'");
            error_log("Connection check: " . (isset($this->connection) ? 'EXISTS' : 'MISSING'));
            
            if (!isset($this->connection)) {
                error_log("CRITICAL ERROR: Database connection not set!");
                return false;
            }
            
            $query = "SELECT id, username, password FROM users WHERE username = ? LIMIT 1";
            error_log("SQL Query: " . $query);
            error_log("Parameter: " . $username);
            
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                $errorInfo = $this->connection->errorInfo();
                $this->lastError = 'Database error: ' . $errorInfo[2];
                error_log("PREPARE ERROR: " . $this->lastError);
                return false;
            }
            
            error_log("Successfully prepared statement");
            
            if ($stmt->execute([$username])) {
                error_log("Execute succeeded, fetching result...");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("RESULT: " . ($row ? 'FOUND USER' : 'NO USER FOUND'));
                error_log("Row data: " . var_export($row, true));
                
                if ($row) {
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    $this->password = $row['password'];
                    error_log("Object set: id={$this->id}, username={$this->username}");
                    return $row;
                }
                error_log("User '$username' does NOT exist in database");
                return false;
            } else {
                $errorInfo = $stmt->errorInfo();
                $this->lastError = 'Query error: ' . $errorInfo[2];
                error_log("EXECUTE ERROR: " . $this->lastError);
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = 'Exception: ' . $e->getMessage();
            error_log("findByUsername EXCEPTION for '$username': " . $this->lastError);
            return false;
        }
    }

    /**
     * Update user password by user ID
     */
    public function updatePassword($userId, $hashedPassword) {
        try {
            $userId = (int)$userId;
            
            if ($userId <= 0) {
                $this->lastError = 'Invalid user ID';
                return false;
            }
            
            $query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                $errorInfo = $this->connection->errorInfo();
                $this->lastError = 'Database error: ' . $errorInfo[2];
                return false;
            }
            
            if ($stmt->execute([$hashedPassword, $userId])) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                $this->lastError = 'Failed to update password: ' . $errorInfo[2];
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = 'Exception: ' . $e->getMessage();
            return false;
        }
    }
}