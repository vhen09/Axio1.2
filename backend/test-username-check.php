<?php
// Test script to check username lookup

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$db = new Database();
$connection = $db->getConnection();

if (!$connection) {
    die("Database connection failed\n");
}

$user = new User($connection);

// Test 1: Check a username that shouldn't exist
echo "=== Test 1: Checking username 'testuser999' (should NOT exist) ===\n";
$result1 = $user->findByUsername('testuser999');
var_dump($result1);

// Test 2: Try to find pogi
echo "\n=== Test 2: Checking username 'pogi' ===\n";
$result2 = $user->findByUsername('pogi');
var_dump($result2);

// Test 3: Check what users actually exist in the database
echo "\n=== Test 3: All users in database ===\n";
$query = "SELECT id, username FROM users";
$stmt = $connection->prepare($query);
if ($stmt && $stmt->execute()) {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    var_dump($rows);
} else {
    echo "Query failed\n";
}

echo "\n=== Test Complete ===\n";
?>
