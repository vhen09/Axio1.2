<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate proof API request
$_GET['action'] = 'get_proof_attempts';
$_POST = [];
$_SERVER['REQUEST_METHOD'] = 'GET';

session_start();

require_once __DIR__ . '/backend/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null || !$database->isConnected()) {
        echo "ERROR: Database connection failed\n";
        echo "isDemoMode: " . ($database->isDemoMode() ? 'YES' : 'NO') . "\n";
    } else {
        echo "SUCCESS: Database connected\n";
        
        if ($database->isDemoMode()) {
            echo "Running in DEMO MODE (SQLite)\n";
        } else {
            echo "Connected to MySQL\n";
        }
        
        // Try to get theorems
        $stmt = $db->query("SELECT id, name FROM theorems LIMIT 3");
        $theorems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample theorems: " . json_encode($theorems) . "\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
?>
