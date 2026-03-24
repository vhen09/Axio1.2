<?php
class Database {
    private $host = 'localhost';
    private $db = 'lean4_ai_app';
    private $user = 'root';
    private $pass = '';
    private $pdo = null;
    private $demoMode = false;

    public function __construct() {
        error_log("=== Database Connection Attempt ===");
        if (getenv('DATABASE_URL')) {
            $this->connectFromUrl(getenv('DATABASE_URL'));
        } elseif (getenv('DB_HOST')) {
            $this->host = getenv('DB_HOST');
            $this->db = getenv('DB_NAME') ?? 'lean4_ai_app';
            $this->user = getenv('DB_USER') ?? 'root';
            $this->pass = getenv('DB_PASS') ?? '';
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
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->db}", $this->user, $this->pass);
        } catch (Exception $e) {
            $this->demoMode = true;
            error_log("Local DB fail: " . $e->getMessage());
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

