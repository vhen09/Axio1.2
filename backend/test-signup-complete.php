<?php
/**
 * Complete Signup Test
 * Tests the full signup flow to ensure users are saved to database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/api/auth.php';

$testUsername = 'testuser_' . time();
$testPassword = 'TestPassword123!';

echo "==== COMPLETE SIGNUP TEST ====\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Check database connection
echo "TEST 1: Database Connection\n";
echo str_repeat("-", 40) . "\n";

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    if ($db->isDemoMode()) {
        echo "⚠️  WARNING: Demo mode is ENABLED!\n";
        echo "This means signups will NOT be saved to database.\n";
        echo "You must have valid DATABASE_URL or database credentials.\n";
    } else {
        echo "✓ Production mode - Database available\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check existing users before signup
echo "\n\nTEST 2: Users Before Signup\n";
echo str_repeat("-", 40) . "\n";

try {
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $countBefore = $result['count'];
    echo "Users in database: $countBefore\n";
    
    // Show last 5 users
    $stmt = $connection->prepare("SELECT id, username FROM users ORDER BY id DESC LIMIT 5");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($users)) {
        echo "Recent users:\n";
        foreach ($users as $user) {
            echo "  - ID {$user['id']}: {$user['username']}\n";
        }
    }
} catch (Exception $e) {
    echo "Error reading users: " . $e->getMessage() . "\n";
}

// Test 3: Simulate signup via Auth API
echo "\n\nTEST 3: Simulating Signup (Auth::register)\n";
echo str_repeat("-", 40) . "\n";

echo "Signup details:\n";
echo "  Username: $testUsername\n";
echo "  Password: (hidden)\n\n";

$auth = new Auth();
$signupResponse = $auth->register($testUsername, $testPassword);
$signupData = json_decode($signupResponse, true);

echo "API Response:\n";
echo "  success: " . ($signupData['success'] ? 'YES' : 'NO') . "\n";
echo "  message: " . ($signupData['message'] ?? 'N/A') . "\n";

if ($signupData['success']) {
    echo "  user_id: " . ($signupData['user_id'] ?? 'N/A') . "\n";
    echo "  username: " . ($signupData['username'] ?? 'N/A') . "\n";
}

// Test 4: Check users after signup
echo "\n\nTEST 4: Users After Signup\n";
echo str_repeat("-", 40) . "\n";

try {
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $countAfter = $result['count'];
    
    echo "Users in database: $countAfter\n";
    echo "Difference: " . ($countAfter - $countBefore) . " new user(s)\n";
    
    if ($countAfter > $countBefore) {
        echo "\n✓ SUCCESS: New user was added to database!\n";
        
        // Find the new user
        $stmt = $connection->prepare("SELECT id, username, created_at FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$testUsername]);
        $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($newUser) {
            echo "\nNew User Details:\n";
            echo "  ID: {$newUser['id']}\n";
            echo "  Username: {$newUser['username']}\n";
            echo "  Created: {$newUser['created_at']}\n";
        }
    } else {
        echo "\n✗ ERROR: No new user was added to database!\n";
        echo "The signup either failed or data wasn't saved.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 5: Test login with the new user
echo "\n\nTEST 5: Test Login with New User\n";
echo str_repeat("-", 40) . "\n";

echo "Attempting login with:\n";
echo "  Username: $testUsername\n";
echo "  Password: (hidden)\n\n";

$loginResponse = $auth->login($testUsername, $testPassword);
$loginData = json_decode($loginResponse, true);

echo "Login Response:\n";
echo "  success: " . ($loginData['success'] ? 'YES' : 'NO') . "\n";
echo "  message: " . ($loginData['message'] ?? 'N/A') . "\n";

if ($loginData['success']) {
    echo "  user_id: " . ($loginData['user_id'] ?? 'N/A') . "\n";
    echo "  username: " . ($loginData['username'] ?? 'N/A') . "\n";
    echo "\n✓ LOGIN SUCCESS with newly created user\n";
} else {
    echo "\n✗ LOGIN FAILED\n";
}

// Test 6: Check error logs
echo "\n\nTEST 6: Review Error Logs\n";
echo str_repeat("-", 40) . "\n";

$logPath = __DIR__ . '/logs/system.log';
if (file_exists($logPath)) {
    $logLines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES), -40);
    
    $signupLogs = array_filter($logLines, function($line) {
        return strpos($line, 'Signup') !== false || 
               strpos($line, 'SUCCESS') !== false || 
               strpos($line, 'FAILED') !== false ||
               strpos($line, 'INSERT') !== false;
    });
    
    if (!empty($signupLogs)) {
        echo "Signup-related logs:\n";
        foreach ($signupLogs as $line) {
            echo "  " . $line . "\n";
        }
    } else {
        echo "No signup logs found\n";
    }
} else {
    echo "Log file not found: $logPath\n";
}

echo "\n" . str_repeat("=", 40) . "\n";
echo "TEST COMPLETE\n";
echo "=== SUMMARY ===\n";
echo "If signup succeeded and user count increased, data is being saved.\n";
echo "If signup failed, check the logs above for error details.\n";
?>
