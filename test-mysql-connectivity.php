<?php
error_reporting(E_ALL);

echo "=== MySQL Connection Test ===\n";

// Test 1: Direct PDO connection
echo "\n1. Testing PDO MySQL connection (127.0.0.1)...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=lean4_ai_app", "root", "");
    echo "✓ Connected via 127.0.0.1\n";
    $result = $pdo->query("SELECT 1 as test");
    echo "✓ Query executed\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: localhost connection
echo "\n2. Testing PDO MySQL connection (localhost)...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=lean4_ai_app", "root", "");
    echo "✓ Connected via localhost\n";
    $result = $pdo->query("SELECT 1 as test");
    echo "✓ Query executed\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 3: Check if mysqli is available
echo "\n3. Checking mysqli extension...\n";
if (extension_loaded('mysqli')) {
    echo "✓ mysqli extension is loaded\n";
    
    $mysqli = new mysqli("127.0.0.1", "root", "", "lean4_ai_app");
    if ($mysqli->connect_error) {
        echo "✗ mysqli connection error: " . $mysqli->connect_error . "\n";
    } else {
        echo "✓ mysqli connected successfully\n";
        $mysqli->close();
    }
} else {
    echo "✗ mysqli extension is NOT loaded\n";
}

// Test 4: Network connectivity
echo "\n4. Testing MySQL port connectivity...\n";
$sock = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 2);
if ($sock) {
    echo "✓ Port 3306 is accessible\n";
    fclose($sock);
} else {
    echo "✗ Port 3306 is NOT accessible: [$errno] $errstr\n";
}

echo "\n=== Test Complete ===\n";
?>
