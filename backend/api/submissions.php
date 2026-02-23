<?php
require_once '../config/database.php';
require_once '../services/ValidationService.php';
require_once '../services/LeanService.php';
require_once '../services/DeepSeekService.php';

$database = new Database();
$db = $database->getConnection();

$validationService = new ValidationService();
$leanService = new LeanService();
$deepSeekService = new DeepSeekService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['math_input'])) {
        $mathInput = $input['math_input'];
        
        // Validate the input
        if ($validationService->validate($mathInput)) {
            // Convert to Lean-compatible format
            $leanInput = $leanService->convertToLeanFormat($mathInput);
            
            // Send to Lean for verification
            $verificationResult = $leanService->verify($leanInput);
            
            // Store submission and score in the database
            $submissionId = $leanService->storeSubmission($mathInput, $verificationResult);
            $score = $leanService->calculateScore($verificationResult);
            $leanService->storeScore($submissionId, $score);
            
            // Respond with the score
            echo json_encode(['score' => $score]);
        } else {
            echo json_encode(['error' => 'Invalid input']);
        }
    } else {
        echo json_encode(['error' => 'No input provided']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
?>