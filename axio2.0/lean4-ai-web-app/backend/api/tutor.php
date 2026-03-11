<?php
/**
 * AI Tutor API Endpoint
 * Handles all AI tutoring requests for proof assistance
 */

require_once __DIR__ . '/../services/ProofTutorService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit();
}

$tutorService = new ProofTutorService();

try {
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'get_assistance':
            $theorem = $input['theorem'] ?? '';
            $currentProof = $input['currentProof'] ?? '';
            $context = $input['context'] ?? [];
            
            if (empty($theorem)) {
                throw new Exception('Theorem is required');
            }
            
            $result = $tutorService->getTutoringAssistance($theorem, $currentProof, $context);
            
            // Add assistance field for backward compatibility
            if ($result['success'] && isset($result['response'])) {
                $result['assistance'] = $result['response'];
            }
            
            echo json_encode($result);
            break;
            
        case 'step_by_step':
            $theorem = $input['theorem'] ?? '';
            $currentStep = $input['currentStep'] ?? 0;
            $previousSteps = $input['previousSteps'] ?? [];
            
            if (empty($theorem)) {
                throw new Exception('Theorem is required');
            }
            
            $result = $tutorService->getStepByStepGuidance($theorem, $currentStep, $previousSteps);
            echo json_encode($result);
            break;
            
        case 'verify_step':
            $theorem = $input['theorem'] ?? '';
            $step = $input['step'] ?? '';
            $stepNumber = $input['stepNumber'] ?? 1;
            $previousSteps = $input['previousSteps'] ?? [];
            
            if (empty($theorem) || empty($step)) {
                throw new Exception('Theorem and step are required');
            }
            
            $result = $tutorService->verifyProofStep($theorem, $step, $stepNumber, $previousSteps);
            echo json_encode($result);
            break;
            
        case 'explain_concept':
            $concept = $input['concept'] ?? '';
            $context = $input['context'] ?? '';
            
            if (empty($concept)) {
                throw new Exception('Concept is required');
            }
            
            $result = $tutorService->explainConcept($concept, $context);
            echo json_encode($result);
            break;
            
        case 'suggest_strategy':
            $theorem = $input['theorem'] ?? '';
            $theoremType = $input['theoremType'] ?? '';
            
            if (empty($theorem)) {
                throw new Exception('Theorem is required');
            }
            
            $result = $tutorService->suggestProofStrategy($theorem, $theoremType);
            echo json_encode($result);
            break;
            
        case 'check_proof':
            $theorem = $input['theorem'] ?? '';
            $proof = $input['proof'] ?? '';
            
            if (empty($theorem) || empty($proof)) {
                throw new Exception('Theorem and proof are required');
            }
            
            $result = $tutorService->checkCompleteProof($theorem, $proof);
            echo json_encode($result);
            break;
            
        case 'get_hint':
            $theorem = $input['theorem'] ?? '';
            $currentProof = $input['currentProof'] ?? '';
            $stuckPoint = $input['stuckPoint'] ?? '';
            
            if (empty($theorem) || empty($stuckPoint)) {
                throw new Exception('Theorem and stuck point are required');
            }
            
            $result = $tutorService->getHint($theorem, $currentProof, $stuckPoint);
            echo json_encode($result);
            break;
            
        case 'suggest_related':
            $theorem = $input['theorem'] ?? '';
            $difficulty = $input['difficulty'] ?? 'similar';
            
            if (empty($theorem)) {
                throw new Exception('Theorem is required');
            }
            
            $result = $tutorService->suggestRelatedTheorems($theorem, $difficulty);
            echo json_encode($result);
            break;
            
        case 'continue_conversation':
            $messages = $input['messages'] ?? [];
            
            if (empty($messages)) {
                throw new Exception('Messages are required');
            }
            
            $result = $tutorService->continueConversation($messages);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
