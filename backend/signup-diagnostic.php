<?php
/**
 * Signup Diagnostic Test
 * Simulates the exact registration flow to identify where "already taken" errors come from
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

echo "=== SIGNUP DIAGNOSTIC TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n\n";

// Test 1: Check database connection
echo "TEST 1: Database Connection\n";
echo str_repeat("-", 30) . "\n";
try {
    $db = new Database();
    echo "✓ Database object created\n";
    echo "✓ Demo mode: " . ($db->isDemoMode() ? 'YES' : 'NO') . "\n";
    $connection = $db->getConnection();
    if ($connection) {
        echo "✓ Database connection is valid (PDO)\n";
    } else {
        echo "✗ Database connection is NULL!\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test User model with test usernames
echo "\n\nTEST 2: Username Lookup (findByUsername)\n";
echo str_repeat("-", 30) . "\n";

$testUsernames = ['pogi', 'testuser999', 'admin', 'nonexistent123'];

foreach ($testUsernames as $testUser) {
    $user = new User($connection);
    $result = $user->findByUsername($testUser);
    echo "findByUsername('$testUser'): " . ($result ? 'FOUND' : 'NOT FOUND') . "\n";
    if ($result) {
        echo "  -> User ID: {$result['id']}, Username: {$result['username']}\n";
    }
}

// Test 3: List all users in database
echo "\n\nTEST 3: All Users in Database\n";
echo str_repeat("-", 30) . "\n";
try {
    $stmt = $connection->prepare("SELECT id, username FROM users ORDER BY id");
    $stmt->execute();
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allUsers)) {
        echo "No users found in database!\n";
    } else {
        echo "Total users: " . count($allUsers) . "\n";
        foreach ($allUsers as $u) {
            echo "  - ID {$u['id']}: {$u['username']}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error querying users: " . $e->getMessage() . "\n";
}

// Test 4: Simulate signup for a brand new user
echo "\n\nTEST 4: Simulate Signup Registration\n";
echo str_repeat("-", 30) . "\n";

$testUsername = 'signuptest_' . time();
$testPassword = 'TestPassword123';

echo "Attempting to create user: $testUsername\n";
echo "Password: (hidden)\n";

$user = new User($connection);

// First check it doesn't exist
$checkResult = $user->findByUsername($testUsername);
echo "Pre-check - Username exists? " . ($checkResult ? 'YES' : 'NO') . "\n";

// Try to create the user
$createResult = $user->create($testUsername, $testPassword);
echo "Create user result: " . ($createResult ? 'SUCCESS' : 'FAILED') . "\n";

if (!$createResult) {
    echo "Error: " . $user->getLastError() . "\n";
} else {
    // Verify it was created
    $user2 = new User($connection);
    $verifyResult = $user2->findByUsername($testUsername);
    echo "Post-create verification - Username exists? " . ($verifyResult ? 'YES' : 'NO') . "\n";
}

// Test 5: Simulate the Auth::register() flow
echo "\n\nTEST 5: Simulate Auth::register() API Call\n";
echo str_repeat("-", 30) . "\n";

require_once __DIR__ . '/api/auth.php';

$testUsername2 = 'authtest_' . time();
$testPassword2 = 'AuthTestPass123';

echo "Attempting auth->register('$testUsername2', '...')\n";

$auth = new Auth();
echo "Auth object created, demo mode: " . ($auth->isDemoMode() ? 'YES' : 'NO') . "\n";

$responseJson = $auth->register($testUsername2, $testPassword2);
echo "Response: $responseJson\n";

$parsed = json_decode($responseJson, true);
echo "Parsed response:\n";
echo "  - success: " . ($parsed['success'] ? 'YES' : 'NO') . "\n";
echo "  - message: " . ($parsed['message'] ?? 'N/A') . "\n";
echo "  - user_id: " . ($parsed['user_id'] ?? 'N/A') . "\n";

// Test 6: Check error logs
echo "\n\nTEST 6: Recent Error Logs\n";
echo str_repeat("-", 30) . "\n";

$logPath = __DIR__ . '/logs/system.log';
if (file_exists($logPath)) {
    echo "Reading logs from: $logPath\n";
    $logLines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES), -20);
    echo "Last 20 log entries:\n";
    foreach ($logLines as $line) {
        echo "  $line\n";
    }
} else {
    echo "Log file not found at: $logPath\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSTIC TEST COMPLETE\n";
echo "=== END ===\n";
?>
