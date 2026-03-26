<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test MySQL connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=lean4_ai_app", "root", "");
    echo "SUCCESS: Connected to MySQL\n";
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM theorems");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "Theorems count: " . $row['count'] . "\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trying SQLite fallback...\n";
    
    try {
        $dataDir = __DIR__ . '/data';
        @mkdir($dataDir, 0755, true);
        
        $sqlitePath = $dataDir . '/axio.db';
        $pdo = new PDO('sqlite:' . $sqlitePath);
        echo "SUCCESS: Connected to SQLite\n";
        
    } catch (Exception $e2) {
        echo "ERROR with SQLite: " . $e2->getMessage() . "\n";
    }
}
?>
