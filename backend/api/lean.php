<?php
// This file interfaces with the Lean4 logic engine, sending inputs for verification and receiving results.

require_once '../config/database.php';
require_once '../services/LeanService.php';
require_once '../services/ValidationService.php';

header('Content-Type: application/json');

$leanService = new LeanService();
$validationService = new ValidationService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['mathInput'])) {
        $mathInput = $input['mathInput'];

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
        echo json_encode([
            'success' => false,
            'message' => 'No mathematical input provided.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>