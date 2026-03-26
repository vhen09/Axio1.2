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
            // Default to PostgreSQL (changed from MySQL)
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db}";
            $this->pdo = new PDO(
                $dsn,
                $this->user,
                $this->pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5
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

