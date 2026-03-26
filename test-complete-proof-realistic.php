<?php
// COMPLETE PROOF SUBMISSION TEST
// This simulates what happens when user clicks "Complete Proof" button

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== COMPLETE PROOF SUBMISSION TEST ===\n\n";

// Simulate a valid proof submission with 2+ verified steps
$proofPayload = [
    'action' => 'save_complete_proof',
    'theorem' => 'The sum of two even numbers is even',
    'steps' => [
        [
            'text' => 'Let m and n be two even numbers. By definition of even, m = 2a and n = 2b for some integers a and b.',
            'verified' => true,
            'status' => 'success'
        ],
        [
            'text' => 'Then m + n = 2a + 2b = 2(a + b). Since a + b is an integer, m + n is even by definition. Therefore, the sum of two even numbers is even. ∎',
            'verified' => true,
            'status' => 'success'
        ]
    ],
    'proof_meta' => [
        'started_at' => date('Y-m-d H:i:s'),
        'completed_at' => date('Y-m-d H:i:s'),
        'time_spent_seconds' => 120
    ],
    'is_completed' => true,
    'is_verified' => true,
    'verification_status' => 'success',
    'verification_summary' => 'All steps verified successfully',
    'draft_submission_id' => null,
    'score' => 95
];

echo "Payload Summary:\n";
echo "- Theorem: " . $proofPayload['theorem'] . "\n";
echo "- Steps: " . count($proofPayload['steps']) . " (all verified)\n";
echo "- Action: " . $proofPayload['action'] . "\n\n";

// Test via HTTP POST
echo "Sending to: http://localhost:8080/backend/api/submissions.php\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n\n";

$ch = curl_init('http://localhost:8080/backend/api/submissions.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($proofPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpCode\n";

if ($error) {
    echo "ERROR: $error\n";
} elseif (empty($response)) {
    echo "ERROR: Empty response from server\n";
} else {
    echo "\n=== RESPONSE ===\n";
    echo $response . "\n";
    
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\n=== PARSED RESPONSE ===\n";
        echo "Success: " . ($decoded['success'] ? 'YES ✓' : 'NO ✗') . "\n";
        if (isset($decoded['error'])) {
            echo "Error: " . $decoded['error'] . "\n";
        }
        if (isset($decoded['submission_id'])) {
            echo "Submission ID: " . $decoded['submission_id'] . "\n";
        }
        if (isset($decoded['score'])) {
            echo "Score: " . $decoded['score'] . "\n";
        }
    }
}

echo "\n=== END TEST ===\n";
?>
