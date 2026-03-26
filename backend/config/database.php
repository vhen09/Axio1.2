<?php
class Database {
    private $host = 'localhost';
    private $port = '5432';
    private $db = 'lean4_ai_db';
    private $user = 'lean4_user';
    private $pass = 'lean4_password_123';
    private $dbType = 'postgresql'; // PostgreSQL is now default
    private $pdo = null;
    private $demoMode = false;

    public function __construct() {
        error_log("=== Database Connection Attempt ===");
        if (getenv('DATABASE_URL')) {
            $this->connectFromUrl(getenv('DATABASE_URL'));
        } elseif (getenv('DB_HOST')) {
            $this->host = getenv('DB_HOST');
            $this->port = getenv('DB_PORT') ?? '5432';
            $this->db = getenv('DB_NAME') ?? 'lean4_ai_db';
            $this->user = getenv('DB_USER') ?? 'lean4_user';
            $this->pass = getenv('DB_PASS') ?? 'lean4_password_123';
            $this->dbType = getenv('DB_TYPE') ?? 'postgresql';
            $this->connectWithCredentials();
        } else {
            $this->connectWithCredentials();
        }
        
        if ($this->pdo) {
            error_log("DB OK");
        } else {
            error_log("DB FAILED - demo mode");
        }
    }

    private function connectFromUrl($url) {
        try {
            // Parse DATABASE_URL (works for both MySQL and PostgreSQL from Render)
            $parsed = parse_url($url);
            
            if ($parsed && isset($parsed['scheme'])) {
                $type = $parsed['scheme'];
                $this->user = urldecode($parsed['user'] ?? 'root');
                $this->pass = urldecode($parsed['pass'] ?? '');
                $this->host = $parsed['host'] ?? 'localhost';
                $port = $parsed['port'] ?? ($type === 'postgresql' ? '5432' : '3306');
                $this->db = ltrim($parsed['path'] ?? '', '/');
                
                if ($type === 'postgresql' || $type === 'postgres') {
                    $dsn = "pgsql:host={$this->host};port={$port};dbname={$this->db}";
                } else {
                    $dsn = "mysql:host={$this->host};port={$port};dbname={$this->db};charset=utf8mb4";
                }
                
                error_log("Database: Connecting {$type}://{$this->host}:{$port}/{$this->db}");
                $this->pdo = new PDO($dsn, $this->user, $this->pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                error_log("Database: Connection SUCCESS");
            } else {
                error_log("Database: Invalid URL format");
                $this->tryDemoMode();
            }
        } catch (Exception $e) {
            error_log("Database: Connection FAILED - " . $e->getMessage());
            $this->tryDemoMode();
        }
    }

    private function tryDemoMode() {
        try {
            $path = __DIR__ . '/../../data/axio.db';
            if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
            $this->pdo = new PDO('sqlite:' . $path);
        } catch (Exception $e) {
            $this->demoMode = true;
            error_log("Demo mode: " . $e->getMessage());
        }
    }

    private function connectWithCredentials() {
        try {
            // Check if PostgreSQL extension is available and host is accessible
            if (!extension_loaded('pdo_pgsql')) {
                error_log("Database: PostgreSQL extension not available, using SQLite");
                $this->tryDemoMode();
                return;
            }
            
            // Default to PostgreSQL (changed from MySQL)
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db};connect_timeout=5";
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            error_log("Database: PostgreSQL connected successfully");
        } catch (Exception $e) {
            error_log("Database: PostgreSQL connection failed - " . $e->getMessage());
            error_log("Database: Attempting to use SQLite fallback");
            $this->tryDemoMode();
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
    
    public function isDemoMode() {
        return $this->demoMode;
    }

    public function isConnected() {
        return $this->pdo !== null && !$this->demoMode;
    }
    
    /**
     * Get the correct NOW/CURRENT_TIMESTAMP function for the current database type
     * PostgreSQL: CURRENT_TIMESTAMP
     * SQLite: CURRENT_TIMESTAMP
     * MySQL: CURRENT_TIMESTAMP (or NOW())
     * @return string The current timestamp SQL function
     */
    public function getCurrentTimestampFunction() {
        if (!$this->pdo) {
            return 'CURRENT_TIMESTAMP';
        }
        
        try {
            $dbType = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            // All three databases support CURRENT_TIMESTAMP
            return 'CURRENT_TIMESTAMP';
        } catch (Exception $e) {
            return 'CURRENT_TIMESTAMP';
        }
    }
    
    /**
     * Initialize database schema (creates tables if they don't exist)
     */
    public function initializeSchema() {
        if (!$this->pdo || $this->demoMode) {
            return false;
        }
        
        try {
            $dbType = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            error_log("Initializing schema for $dbType");
            
            if ($dbType === 'pgsql') {
                // PostgreSQL schema
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS users (
                        id SERIAL PRIMARY KEY,
                        username VARCHAR(255) UNIQUE NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        first_name VARCHAR(100),
                        last_name VARCHAR(100),
                        email VARCHAR(255),
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS user_preferences (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER UNIQUE REFERENCES users(id) ON DELETE CASCADE,
                        latex_skill_level VARCHAR(50) DEFAULT 'beginner',
                        tutorial_completed BOOLEAN DEFAULT FALSE,
                        tutorial_skipped BOOLEAN DEFAULT FALSE,
                        onboarding_complete BOOLEAN DEFAULT FALSE
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS submissions (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                        theorem_id INTEGER,
                        input_text TEXT,
                        lean_code TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS scores (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
                        submission_id INTEGER,
                        score INTEGER,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                
            } else if ($dbType === 'sqlite') {
                // SQLite schema
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS users (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username TEXT UNIQUE NOT NULL,
                        password TEXT NOT NULL,
                        first_name TEXT,
                        last_name TEXT,
                        email TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS user_preferences (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER UNIQUE,
                        latex_skill_level TEXT DEFAULT 'beginner',
                        tutorial_completed BOOLEAN DEFAULT 0,
                        tutorial_skipped BOOLEAN DEFAULT 0,
                        onboarding_complete BOOLEAN DEFAULT 0
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS submissions (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER,
                        theorem_id INTEGER,
                        input_text TEXT,
                        lean_code TEXT,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS scores (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_id INTEGER,
                        submission_id INTEGER,
                        score INTEGER,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            }
            
            error_log("✓ Schema initialization complete");
            return true;
            
        } catch (Exception $e) {
            error_log("✗ Schema initialization failed: " . $e->getMessage());
            return false;
        }
    }

    public function query($sql, $params = []) {
        if (!$this->pdo) return false;
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            error_log('Query failed: ' . $e->getMessage());
            return false;
        }
    }
}
?>

