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
        error_log("Is production (has RENDER/DATABASE_URL): " . ((getenv('RENDER') || getenv('DATABASE_URL')) ? 'YES' : 'NO'));
        
        // Check for environment variables (Render, production, etc.)
        if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL')) {
            error_log("Using DATABASE_URL from environment (supports MySQL or PostgreSQL)");
            $url = getenv('DATABASE_URL') ?: $_ENV['DATABASE_URL'];
            error_log("DATABASE_URL length: " . strlen($url) . " chars");
            $this->connectFromUrl($url);
        } elseif (isset($_ENV['DB_HOST']) || getenv('DB_HOST')) {
            error_log("Using individual database environment variables");
            $this->host = getenv('DB_HOST') ?: $_ENV['DB_HOST'];
            $this->db = getenv('DB_NAME') ?: $_ENV['DB_NAME'] ?? 'lean4_ai_app';
            $this->user = getenv('DB_USER') ?: $_ENV['DB_USER'] ?? 'root';
            $this->pass = getenv('DB_PASS') ?: $_ENV['DB_PASS'] ?? '';
            $this->connectWithCredentials();
        } else {
            // Fallback to localhost for local development
            error_log("No production env vars found, using localhost defaults (local development)");
            $this->connectWithCredentials();
        }
        
        // Log final status
        if ($this->pdo) {
            error_log("✓ Database connection: SUCCESS");
        } else {
            error_log("✗ Database connection: FAILED - Demo mode enabled");
            error_log("This may be temporary database unavailability or wrong credentials");
        }
    }

    private function connectFromUrl($url) {
        try {
            // Parse DATABASE_URL: mysql://user:password@host:port/dbname OR postgresql://user:password@host:port/dbname
            $dbType = 'mysql'; // default
            
            if (preg_match('/^(mysql|postgresql):\/\/([^:]+):(.*)@([^:\/]+)(?::(\d+))?\/(.+)$/', $url, $matches)) {
                $dbType = $matches[1];
                $this->user = urldecode($matches[2]);
                $this->pass = urldecode($matches[3]);
                $this->host = $matches[4];
                $port = $matches[5] ?? ($dbType === 'postgresql' ? 5432 : 3306);
                $this->db = $matches[6];
                
                // Build DSN based on database type
                if ($dbType === 'postgresql') {
                    $dsn = "pgsql:host={$this->host};port={$port};dbname={$this->db}";
                    error_log("Attempting PostgreSQL connection to {$this->host}:{$port}");
                } else {
                    $dsn = "mysql:host={$this->host};port={$port};dbname={$this->db};charset=utf8mb4";
                    error_log("Attempting MySQL connection to {$this->host}:{$port}");
                }
                
                $this->pdo = new PDO($dsn, $this->user, $this->pass, 
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                error_log("✓ Connected via {$dbType}");
            } else {
                throw new Exception('Invalid DATABASE_URL format. Expected: mysql://user:pass@host:port/db or postgresql://user:pass@host:port/db');
            }
        } catch (PDOException | Exception $e) {
            error_log("Connection error: " . $e->getMessage());
            // Try SQLite fallback if available
            $this->tryDemoMode();
        }
    }

    private function tryDemoMode() {
        try {
            // Try SQLite as fallback for development
            $sqlitePath = __DIR__ . '/../../data/axio.db';
            if (!is_dir(dirname($sqlitePath))) {
                mkdir(dirname($sqlitePath), 0755, true);
            }
            
            $this->pdo = new PDO('sqlite:' . $sqlitePath, null, null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            error_log("✓ Fallback to SQLite database");
        } catch (PDOException $e) {
            error_log('SQLite fallback also failed, demo mode enabled: ' . $e->getMessage());
            $this->demoMode = true;
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