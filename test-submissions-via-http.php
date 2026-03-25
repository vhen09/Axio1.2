<?php
// Simple test to POST to submissions.php and see what the actual error is
error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = 'http://localhost:8080/backend/api/submissions.php';

$data = json_encode([
    'proof_text' => 'Test proof here',
    'theorem' => 'Test Theorem',
    'steps' => 1,
    'score' => 50
]);

echo "=== Testing submissions.php via HTTP ===\n";
echo "URL: $url\n";
echo "Method: POST\n";
echo "Data: $data\n\n";

// Use cURL to test
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_VERBOSE, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $httpCode\n";
echo "Response Length: " . strlen($response) . " bytes\n\n";

if ($error) {
    echo "cURL Error: $error\n";
} else {
    echo "Response:\n";
    echo $response . "\n";
    
    if (!empty($response)) {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "\nParsed JSON:\n";
            print_r($decoded);
        } else {
            echo "\nJSON Parse Error: " . json_last_error_msg() . "\n";
        }
    }
}
?>
