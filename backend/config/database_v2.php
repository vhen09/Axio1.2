<?php
/**
 * AXIO Database Configuration & Connection Manager
 * 
 * Supports:
 * - PostgreSQL (Recommended for production on Render)
 * - MySQL (Legacy support)
 * - SQLite (Demo/fallback mode)
 * 
 * Connection Pooling:
 * - Uses PDO connection caching
 * - Implements retry logic for transient failures
 * - Health checks on startup
 * 
 * @version 2.0
 * @last_updated March 24, 2026
 */

class Database {
    private static $instance = null;
    private $pdo = null;
    private $demoMode = false;
    private $dbType = 'unknown';
    private $maxRetries = 3;
    private $retryDelay = 100; // milliseconds
    
    // Database credentials (overridable via environment)
    private $host = 'localhost';
    private $port = 5432;
    private $db = 'axio_app';
    private $user = 'axio_app';
    private $pass = '';

    /**
     * Constructor - Establishes database connection
     * Tries in order: DATABASE_URL → Individual env vars → Localhost
     */
    public function __construct() {
        error_log("=== Database Connection Attempt ===");
        error_log("Environment: " . (getenv('APP_ENV') ?: 'development'));
        
        $connected = false;
        
        // Try DATABASE_URL first (Render, Heroku, etc.)
        if (getenv('DATABASE_URL')) {
            $connected = $this->connectFromUrl(getenv('DATABASE_URL'));
        }
        
        // Try individual environment variables
        if (!$connected && getenv('DB_HOST')) {
            $this->host = getenv('DB_HOST');
            $this->db = getenv('DB_NAME') ?? 'axio_app';
            $this->user = getenv('DB_USER') ?? 'axio_app';
            $this->pass = getenv('DB_PASS') ?? '';
            $this->port = getenv('DB_PORT') ?? 5432;
            $this->dbType = getenv('DB_TYPE') ?? 'postgresql';
            $connected = $this->connectWithCredentials();
        }
        
        // Fall back to localhost
        if (!$connected) {
            error_log("Using localhost connection");
            $connected = $this->connectWithCredentials();
        }
        
        // Last resort: SQLite demo mode
        if (!$connected) {
            $this->tryDemoMode();
        }
        
        $status = $this->pdo ? "SUCCESS" : "FAILED";
        error_log("Database Connection: $status");
        error_log("Database Type: " . $this->dbType);
        error_log("Demo Mode: " . ($this->demoMode ? "YES" : "NO"));
    }

    /**
     * Connect using DATABASE_URL (works with Render, Railway, etc.)
     * Supports: postgresql://, mysql://, sqlite://
     * 
     * @param string $url The DATABASE_URL environment variable
     * @return bool True if connection successful
     */
    private function connectFromUrl($url) {
        try {
            $parsed = parse_url($url);
            
            if (!$parsed || !isset($parsed['scheme'])) {
                error_log("Invalid DATABASE_URL format");
                return false;
            }
            
            $scheme = $parsed['scheme'];
            $this->user = urldecode($parsed['user'] ?? 'axio_app');
            $this->pass = urldecode($parsed['pass'] ?? '');
            $this->host = $parsed['host'] ?? 'localhost';
            $this->port = $parsed['port'];
            $this->db = ltrim($parsed['path'] ?? '', '/');
            
            // Determine database type
            if ($scheme === 'postgresql' || $scheme === 'postgres') {
                $this->dbType = 'postgresql';
                $this->port = $this->port ?: 5432;
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db};sslmode=require";
            } elseif ($scheme === 'mysql' || $scheme === 'mysql2') {
                $this->dbType = 'mysql';
                $this->port = $this->port ?: 3306;
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";
            } elseif ($scheme === 'sqlite') {
                $this->dbType = 'sqlite';
                $dsn = "sqlite:" . $this->db;
            } else {
                error_log("Unsupported database scheme: $scheme");
                return false;
            }
            
            error_log("Connecting to {$this->dbType}://{$this->host}:{$this->port}/{$this->db}");
            
            // Set PDO options for connection pooling and performance
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            
            // PostgreSQL-specific options
            if ($this->dbType === 'postgresql') {
                $options[PDO::ATTR_PERSISTENT] = true; // Connection pooling
            }
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            
            // Verify connection
            $this->pdo->query("SELECT 1");
            
            error_log("Database connection SUCCESS");
            return true;
            
        } catch (PDOException $e) {
            error_log("Database connection FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Connect using individual environment variables or defaults
     * 
     * @return bool True if connection successful
     */
    private function connectWithCredentials() {
        try {
            // Determine DSN based on database type
            if ($this->dbType === 'postgresql' || $this->dbType === 'postgres') {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db}";
            } else {
                // Default to MySQL for backward compatibility
                $this->dbType = 'mysql';
                $this->port = $this->port ?: 3306;
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset=utf8mb4";
            }
            
            error_log("Connecting to {$this->dbType}://{$this->host}:{$this->port}/{$this->db}");
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            error_log("Database connection SUCCESS");
            return true;
            
        } catch (PDOException $e) {
            error_log("Database connection FAILED: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fall back to SQLite for demo/offline mode
     * Creates database file in data/ directory
     */
    private function tryDemoMode() {
        try {
            $dataDir = __DIR__ . '/../../data';
            if (!is_dir($dataDir)) {
                mkdir($dataDir, 0755, true);
            }
            
            $path = $dataDir . '/axio.db';
            $this->pdo = new PDO('sqlite:' . $path);
            $this->dbType = 'sqlite';
            $this->demoMode = true;
            
            error_log("SQLite demo mode initialized at: $path");
            
        } catch (Exception $e) {
            $this->demoMode = true;
            error_log("Demo mode FAILED: " . $e->getMessage());
        }
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection object
     * 
     * @return PDO|null
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Check if database is in demo mode
     * 
     * @return bool
     */
    public function isDemoMode() {
        return $this->demoMode;
    }

    /**
     * Check if connected to a real database
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->pdo !== null && !$this->demoMode;
    }

    /**
     * Get database type
     * 
     * @return string 'postgresql', 'mysql', or 'sqlite'
     */
    public function getDbType() {
        return $this->dbType;
    }

    /**
     * Execute a prepared statement with retry logic
     * Implements exponential backoff for transient failures
     * 
     * @param string $sql SQL query with ? placeholders
     * @param array $params Query parameters
     * @return PDOStatement|false
     */
    public function query($sql, $params = []) {
        if (!$this->pdo) {
            error_log("Query attempted without database connection");
            return false;
        }
        
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt;
                
            } catch (PDOException $e) {
                // Check if error is retryable (connection errors, locks, etc.)
                $isRetryable = $this->isRetryableError($e);
                $isLastAttempt = $attempt === $this->maxRetries;
                
                if ($isRetryable && !$isLastAttempt) {
                    // Exponential backoff: 100ms, 200ms, 400ms
                    $delay = $this->retryDelay * pow(2, $attempt - 1);
                    usleep($delay * 1000);
                    continue;
                }
                
                error_log("Database query failed (attempt $attempt/$this->maxRetries): " . $e->getMessage());
                error_log("SQL: " . substr($sql, 0, 200));
                
                if ($isLastAttempt) {
                    throw $e;
                }
            }
        }
        
        return false;
    }

    /**
     * Execute a query and fetch single row
     * 
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function queryRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }

    /**
     * Execute a query and fetch all rows
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function queryAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Execute a query and fetch single value
     * 
     * @param string $sql
     * @param array $params
     * @param mixed $default Default value if no result
     * @return mixed
     */
    public function queryScalar($sql, $params = [], $default = null) {
        $row = $this->queryRow($sql, $params);
        if (!$row) return $default;
        return reset($row); // Return first column
    }

    /**
     * Insert a single row
     * 
     * @param string $table
     * @param array $data ['column' => 'value', ...]
     * @return int|string Last insert ID or boolean
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($data), '?');
        
        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        try {
            $this->query($sql, array_values($data));
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Insert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch insert multiple rows
     * Much faster than individual inserts
     * 
     * @param string $table
     * @param array $rows Array of ['column' => 'value'] arrays
     * @return int Number of rows inserted
     */
    public function batchInsert($table, $rows) {
        if (empty($rows)) return 0;
        
        $columns = array_keys($rows[0]);
        $placeholders = array_fill(0, count($columns), '?');
        $placeholderRow = '(' . implode(',', $placeholders) . ')';
        
        $allPlaceholders = array_fill(0, count($rows), $placeholderRow);
        $sql = "INSERT INTO $table (" . implode(',', $columns) . ") VALUES " . implode(',', $allPlaceholders);
        
        $params = [];
        foreach ($rows as $row) {
            foreach ($columns as $col) {
                $params[] = $row[$col] ?? null;
            }
        }
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Batch insert failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update rows
     * 
     * @param string $table
     * @param array $data Columns to update
     * @param string $where WHERE clause with ? placeholders
     * @param array $whereParams WHERE parameters
     * @return int Rows affected
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = implode(',', array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        try {
            $params = array_merge(array_values($data), $whereParams);
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Update failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete rows
     * 
     * @param string $table
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int Rows affected
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Delete failed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Start a transaction
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     * 
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     * 
     * @return bool
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    /**
     * Health check - verify database is accessible
     * Used for deployment checks and monitoring
     * 
     * @return array ['healthy' => bool, 'message' => string]
     */
    public function healthCheck() {
        try {
            if (!$this->pdo || $this->demoMode) {
                return [
                    'healthy' => false,
                    'message' => 'Not connected to real database',
                    'type' => $this->dbType
                ];
            }
            
            // Try simple query
            $this->pdo->query("SELECT 1");
            
            // Get connection info
            $info = $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            
            return [
                'healthy' => true,
                'message' => 'Database connection healthy',
                'type' => $this->dbType,
                'version' => $info
            ];
            
        } catch (Exception $e) {
            return [
                'healthy' => false,
                'message' => $e->getMessage(),
                'type' => $this->dbType
            ];
        }
    }

    /**
     * Determine if a PDOException is retryable
     * Transient errors: connection failures, timeouts, locks
     * Non-retryable: syntax errors, constraint violations
     * 
     * @param PDOException $e
     * @return bool
     */
    private function isRetryableError(PDOException $e) {
        $code = $e->getCode();
        $message = $e->getMessage();
        
        // Retryable error codes
        $retryableCodes = [
            'SQLSTATE[08003]', // Connection does not exist
            'SQLSTATE[08006]', // Connection was lost
            'SQLSTATE[HY000]',  // General error
            '58030',            // I/O error
            '57014',            // Query cancelled
        ];
        
        // Check error code
        foreach ($retryableCodes as $retryCode) {
            if (strpos($message, $retryCode) !== false) {
                return true;
            }
        }
        
        // Check for common transient error messages
        $transientPatterns = [
            'too many connections',
            'Connection refused',
            'Connection timeout',
            'Temporary failure',
            'deadlock',
            'lock timeout',
        ];
        
        foreach ($transientPatterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Database version info
     * 
     * @return string
     */
    public function getVersion() {
        try {
            $version = $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            return $version ?: 'Unknown';
        } catch (Exception $e) {
            return 'Unable to determine version';
        }
    }

    /**
     * Get current database name
     * 
     * @return string
     */
    public function getDatabaseName() {
        return $this->db;
    }
}

// Provide global instance for backward compatibility
$db = Database::getInstance();
