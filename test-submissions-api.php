<?php
// Direct test of submissions.php with mock input
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up mock POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Mock the input stream
$jsonInput = json_encode([
    'proof_text' => 'Test proof',
    'theorem' => 'Test Theorem',
    'steps' => 1,
    'score' => 50
]);

// Create a temp stream
$stream = fopen('php://memory', 'r+');
fwrite($stream, $jsonInput);
rewind($stream);

// Test the connection first
require_once __DIR__ . '/backend/config/database.php';

echo "=== Testing Database Connection ===\n";
$database = new Database();
$db = $database->getConnection();

if ($db === null) {
    echo "✗ Database is NULL\n";
} else {
    echo "✓ Database connected\n";
}

echo "isDemoMode: " . ($database->isDemoMode() ? 'YES' : 'NO') . "\n";
echo "isConnected: " . ($database->isConnected() ? 'YES' : 'NO') . "\n";

// Now test the actual submissions.php logic
echo "\n=== Testing Submissions API Logic ===\n";

if (!$db) {
    echo "ERROR: Database connection failed - would return 503 error\n";
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'error' => 'Database is unavailable. Please ensure your database is configured properly and accessible. Contact support if the issue persists.'
    ]);
} else {
    echo "SUCCESS: Database available, would process request\n";
    
    // Try a simple query
    try {
        $stmt = $db->query("SELECT 1");
        if ($stmt) {
            echo "✓ Simple query executed successfully\n";
        } else {
            echo "✗ Simple query failed\n";
        }
    } catch (Exception $e) {
        echo "✗ Exception: " . $e->getMessage() . "\n";
    }
}

fclose($stream);
?>
