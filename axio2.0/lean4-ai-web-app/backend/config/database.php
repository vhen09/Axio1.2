<?php
class Database {
    private $host = 'localhost';
    private $db = 'lean4_ai_app';
    private $user = 'root';
    private $pass = '';
    private $pdo = null;
    private $demoMode = false;

    public function __construct() {
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