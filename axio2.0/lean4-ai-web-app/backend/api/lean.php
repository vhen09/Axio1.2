<?php
// This file interfaces with the Lean4 logic engine, sending inputs for verification and receiving results.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/LeanService.php';
require_once __DIR__ . '/../services/ValidationService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$leanService = new LeanService();
$validationService = new ValidationService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON payload.'
        ]);
        exit();
    }

    $action = $input['action'] ?? 'verify';
    $mathInput = $input['mathInput'] ?? $input['lean_code'] ?? $input['proof'] ?? '';

    if (in_array($action, ['verify', 'verify_input', 'verify_proof'], true)) {
        if (!is_string($mathInput)) {
            $mathInput = strval($mathInput);
        }

        // Validate the input
        if ($validationService->validate($mathInput)) {
            // Send the input to Lean for verification
            $verificationResult = $leanService->verify($mathInput);

            echo json_encode([
                'success' => true,
                'result' => $verificationResult
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid input format.'
            ]);
        }
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>