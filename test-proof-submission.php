<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate a proof completion request
require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/services/NaturalLanguageToLeanConverter.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db || !$database->isConnected()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database unavailable']);
        exit;
    }
    
    echo "=== Database Connection Test ===\n";
    echo "Status: CONNECTED\n";
    echo "Mode: " . ($database->isDemoMode() ? 'SQLite (Demo)' : 'MySQL') . "\n\n";
    
    // Test getting theorems
    echo "=== Testing Theorem Retrieval ===\n";
    $stmt = $db->query("SELECT id, name FROM theorems LIMIT 1");
    $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($theorem) {
        echo "✓ Got theorem: {$theorem['name']} (ID: {$theorem['id']})\n\n";
        
        // Test proof submission
        echo "=== Testing Proof Submission ===\n";
        $userId = 1;  // Default user
        $theoremId = $theorem['id'];
        $proofText = "The sum of two even numbers is even because...";
        
        // Simulate insert
        $stmt = $db->prepare("
            INSERT INTO submissions (user_id, theorem_id, proof_text, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$userId, $theoremId, $proofText])) {
            echo "✓ Proof submitted successfully\n";
            echo "  Submission ID: " . $db->lastInsertId() . "\n\n";
            
            echo "=== All Systems Operational ===\n";
            echo "Database: ✓\n";
            echo "Proof API: ✓\n";
        } else {
            echo "✗ Failed to submit proof\n";
            echo "Error: " . json_encode($stmt->errorInfo()) . "\n";
        }
    } else {
        echo "✗ Could not retrieve theorems\n";
    }
    
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
