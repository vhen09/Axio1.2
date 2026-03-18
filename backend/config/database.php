<?php
class Database {
    private $host = 'localhost';
    private $db = 'lean4_ai_app';
    private $user = 'root';
    private $pass = '';
    private $pdo = null;
    private $demoMode = false;

    public function __construct() {
        // Check for environment variables (Render, production, etc.)
        if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL')) {
            $this->connectFromUrl(getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL']);
        } elseif (isset($_ENV['DB_HOST']) || getenv('DB_HOST')) {
            $this->host = getenv('DB_HOST') ?: $_ENV['DB_HOST'];
            $this->db = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'lean4_ai_app';
            $this->user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'root';
            $this->pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '';
            $this->connectWithCredentials();
        } else {
            // Fallback to localhost for local development
            $this->connectWithCredentials();
        }
        
        // CRITICAL SECURITY: If DB connection failed in production/Render environment, 
        // REFUSE to start in demo mode (which accepts any credentials!)
        $isProduction = isset($_ENV['RENDER']) || getenv('RENDER') || 
                       isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL');
        if ($this->demoMode && $isProduction) {
            // Log the authentication failure
            error_log('SECURITY ALERT: Database connection failed on production/Render environment.');
            error_log('DATABASE_URL: ' . (getenv('DATABASE_URL') ? 'SET' : 'NOT SET'));
            error_log('REFUSING to start in demo mode on production. Please check database configuration.');
            // Return error instead of silently proceeding
            throw new Exception('Database connection failed on production environment. System cannot start in demo mode.');
        }
    }

    private function connectFromUrl($url) {
        try {
            // Parse DATABASE_URL: mysql://user:password@host:port/dbname
            if (preg_match('/^mysql:\/\/([^:]+):(.*)@([^:\/]+)(?::(\d+))?\/(.+)$/', $url, $matches)) {
                $this->user = urldecode($matches[1]);
                $this->pass = urldecode($matches[2]);
                $this->host = $matches[3];
                $port = $matches[4] ?? 3306;
                $this->db = $matches[5];
                
                $dsn = "mysql:host={$this->host};port={$port};dbname={$this->db};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->user, $this->pass, 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
            } else {
                throw new Exception('Invalid DATABASE_URL format');
            }
        } catch (PDOException | Exception $e) {
            $this->demoMode = true;
            error_log('Database connection failed with URL, running in DEMO MODE: ' . $e->getMessage());
        }
    }

    private function connectWithCredentials() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4", 
                $this->user, 
                $this->pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // Enable demo mode when database is not available
            $this->demoMode = true;
            error_log('Database connection failed, running in DEMO MODE: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
    
    public function isDemoMode() {
        return $this->demoMode;
    }

    public function query($sql, $params = []) {
        if (!$this->pdo) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage());
            return false;
        }
    }
}
?>