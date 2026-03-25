<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate a complete_proof_flow API request
$_GET['action'] = 'complete_proof_flow';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Simulate POST data
$postData = json_encode([
    'theorem_id' => 1,
    'natural_language_proof' => 'If n is even, then n = 2k for some integer k. Similarly, if m is even, then m = 2j. So n + m = 2k + 2j = 2(k+j), which is even.'
]);

// Override php://input
$GLOBALS['mock_input'] = $postData;

require_once __DIR__ . '/backend/config/database.php';
require_once __DIR__ . '/backend/services/NaturalLanguageToLeanConverter.php';
require_once __DIR__ . '/backend/services/LeanService.php';
require_once __DIR__ . '/backend/services/Logger.php';

session_start();
session_id('test_session');

try {
    $database = new Database();
    $db = $database->getConnection();
    $converter = new NaturalLanguageToLeanConverter();
    $leanService = new LeanService();
    $logger = new Logger();

    // Mock file_get_contents for php://input
    if (function_exists('stream_get_contents')) {
        $postData = json_encode([
            'theorem_id' => 1,
            'natural_language_proof' => 'If n is even, then n = 2k...'
        ]);
    }
    
    // Manually call completeProofFlow logic
    $data = json_decode($postData, true);
    
    $theorem_id = $data['theorem_id'] ?? null;
    $natural_language_proof = $data['natural_language_proof'] ?? null;
    $_SESSION['user_id'] = 1;  // Set test user
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$natural_language_proof) {
        throw new Exception('Natural language proof is required');
    }
    
    echo "=== Proof Completion Test ===\n";
    echo "Theorem ID: " . ($theorem_id ?: 'Not provided') . "\n";
    echo "User ID: " . ($user_id ?: 'Not provided') . "\n";
    echo "Proof text length: " . strlen($natural_language_proof) . "\n\n";
    
    // Step 1: Get theorem
    $response = ['success' => true, 'steps' => []];
    
    if ($theorem_id && $db instanceof PDO) {
        $query = "SELECT * FROM theorems WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$theorem_id]);
        $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($theorem) {
            echo "✓ Theorem found: {$theorem['name']}\n";
            $theoremContext = $theorem;
        } else {
            echo "✗ Theorem not found\n";
        }
    }
    
    // Step 2: Try to save proof attempt
    if ($user_id && $theorem_id && $db instanceof PDO) {
        echo "\n=== Saving Proof Attempt ===\n";
        
        $insertQuery = "INSERT INTO proof_attempts 
                       (user_id, theorem_id, natural_language_input, generated_lean_code, 
                        verification_status, verification_output, score) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $db->prepare($insertQuery);
        
        $result = $insertStmt->execute([
            $user_id,
            $theorem_id,
            $natural_language_proof,
            'proof code here',
            'pending',
            '{}',
            0
        ]);
        
        if ($result) {
            echo "✓ Proof saved! ID: " . $db->lastInsertId() . "\n";
        } else {
            echo "✗ Failed to save proof\n";
            echo "Error: " . json_encode($insertStmt->errorInfo()) . "\n";
        }
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
