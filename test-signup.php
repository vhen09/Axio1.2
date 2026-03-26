<?php
// Test signup after migration
require_once 'backend/config/database.php';
require_once 'backend/models/User.php';
require_once 'backend/api/auth.php';

echo "=== TESTING SIGNUP ===\n\n";

$testUsername = 'testuser_' . time();
$testPassword = 'TestPassword123';

echo "Creating test user: $testUsername\n";
echo "Email: test@example.com\n";
echo "Name: Juan Dela Cruz\n\n";

// Test via Auth class
$auth = new Auth();

if ($auth->isDemoMode()) {
    echo "ERROR: Database is in demo mode!\n";
    exit(1);
}

echo "Calling Auth::register()...\n";
$response = json_decode($auth->register($testUsername, $testPassword, 'Juan', 'Dela Cruz', 'test@example.com'), true);

echo "Response:\n";
echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

if ($response['success']) {
    echo "✓ SIGNUP SUCCESS!\n";
    echo "✓ User ID: {$response['user_id']}\n";
    echo "✓ Username: {$response['username']}\n";
} else {
    echo "✗ SIGNUP FAILED\n";
    echo "✗ Error: {$response['message']}\n";
}
?>
