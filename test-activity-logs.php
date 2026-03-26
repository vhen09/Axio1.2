<?php
/**
 * Test Activity Logs System
 * Verifies that user activities are being tracked and retrieved correctly
 */

// Database connection
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/models/ActivityLog.php';

echo "=== Activity Logs Test ===\n";

$db = new Database();
$connection = $db->getConnection();

if (!$connection) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "✓ Database connected\n";

$activityLog = new ActivityLog($connection);

// Test 1: Get all activities in database
echo "\n--- Test 1: Retrieve all activities ---\n";
try {
    $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total activities found: " . count($activities) . "\n";
    
    foreach ($activities as $activity) {
        echo "  - User {$activity['user_id']}: {$activity['action']} ({$activity['resource_type']}) at {$activity['created_at']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test 2: Check if there are any users
echo "\n--- Test 2: Check users in database ---\n";
try {
    $query = "SELECT id, username FROM users LIMIT 10";
    $stmt = $connection->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total users found: " . count($users) . "\n";
    
    foreach ($users as $user) {
        echo "  - User {$user['id']}: {$user['username']}\n";
        
        // Get activity stats for this user
        $userStats = $activityLog->getUserStats($user['id']);
        if ($userStats) {
            echo "    Stats: " . json_encode($userStats) . "\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Test manual logging
echo "\n--- Test 3: Test manual activity logging ---\n";
try {
    // Log a test activity
    $testUserId = 1; // Adjust based on existing users
    $result = $activityLog->log(
        $testUserId,
        'test_action',
        'test_resource',
        123,
        ['test_data' => 'test_value'],
        '127.0.0.1'
    );
    
    echo "✓ Test activity logged successfully\n";
    
    // Retrieve it
    $activities = $activityLog->getUserActivities($testUserId, 10, 0);
    if (count($activities) > 0) {
        echo "✓ Successfully retrieved user activities\n";
        echo "  Most recent: " . $activities[0]['action'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
