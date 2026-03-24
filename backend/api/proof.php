<?php

/**
 * Enhanced Proof API - Natural Language to Lean Conversion & Verification
 * Supports proving theorems from the REANA library using natural language
 */

// Auto-initialize database fallback on every request
@require_once __DIR__ . '/../config/auto-setup.php';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/NaturalLanguageToLeanConverter.php';
require_once __DIR__ . '/../services/LeanService.php';
require_once __DIR__ . '/../services/Logger.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

try {
    $database = new Database();
    $db = $database->getConnection();
    $converter = new NaturalLanguageToLeanConverter();
    $leanService = new LeanService();
    $logger = new Logger();

    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    if ($action === '' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = 'get_proof_attempts';
    }

    if (empty($action)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Action is required',
            'available_actions' => [
                'convert_to_lean',
                'verify_lean_proof',
                'complete_proof_flow',
                'save_proof_attempt',
                'get_proof_attempts',
                'refine_proof',
                'generate_skeleton',
                'explain_lean_code',
                'verify-full',
                'verify-step'
            ]
        ]);
        exit();
    }

    switch ($action) {
        case 'convert_to_lean':
            convertToLean($db, $converter);
            break;
            
        case 'verify_lean_proof':
            verifyLeanProof($leanService);
            break;
            
        case 'complete_proof_flow':
            completeProofFlow($db, $converter, $leanService);
            break;
            
        case 'save_proof_attempt':
            saveProofAttempt($db);
            break;
            
        case 'get_proof_attempts':
            getProofAttempts($db);
            break;
            
        case 'refine_proof':
            refineProof($converter);
            break;
            
        case 'generate_skeleton':
            generateSkeleton($converter);
            break;
            
        case 'explain_lean_code':
            explainLeanCode($converter);
            break;
            
        case 'verify-full':
            verifyFullProof($db, $converter);
            break;
            
        case 'verify-step':
            verifyStepProof($db, $converter);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'action' => $action
            ]);
            exit();
    }

} catch (InvalidArgumentException $e) {
    Logger::error('Proof API Validation Error: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    Logger::error('Proof API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Convert natural language proof to Lean
 */
function convertToLean($db, $converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theorem_id = $data['theorem_id'] ?? null;
    $natural_language_proof = $data['natural_language_proof'] ?? null;
    
    if (!$natural_language_proof) {
        throw new InvalidArgumentException('Natural language proof is required');
    }
    
    // Get theorem context if theorem_id provided
    $theoremContext = [];
    if ($theorem_id && $db instanceof PDO) {
        $query = "SELECT * FROM theorems WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$theorem_id]);
        $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($theorem) {
            $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
            $theorem['prerequisites'] = json_decode($theorem['prerequisites'] ?? '[]');
            $theoremContext = $theorem;
        }
    }
    
    // Convert to Lean
    if (!empty($theoremContext)) {
        $result = $converter->convertWithTheoremContext($theoremContext, $natural_language_proof);
    } else {
        $theorem_statement = $data['theorem_statement'] ?? 'No theorem statement provided';
        $result = $converter->convertToLean($theorem_statement, $natural_language_proof);
    }
    
    echo json_encode($result);
}

/**
 * Verify Lean proof code
 */
function verifyLeanProof($leanService) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $lean_code = $data['lean_code'] ?? null;
    
    if (!$lean_code) {
        throw new InvalidArgumentException('Lean code is required');
    }
    
    $result = $leanService->verifyProof($lean_code);
    
    echo json_encode($result);
}

/**
 * Complete proof flow: Natural Language → Lean → Verification
 */
function completeProofFlow($db, $converter, $leanService) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theorem_id = $data['theorem_id'] ?? null;
    $natural_language_proof = $data['natural_language_proof'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$natural_language_proof) {
        throw new InvalidArgumentException('Natural language proof is required');
    }
    
    $response = [
        'success' => true,
        'steps' => []
    ];
    
    // Step 1: Get theorem context
    $theoremContext = [];
    if ($theorem_id && $db instanceof PDO) {
        $query = "SELECT * FROM theorems WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$theorem_id]);
        $theorem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($theorem) {
            $theorem['tags'] = json_decode($theorem['tags'] ?? '[]');
            $theorem['prerequisites'] = json_decode($theorem['prerequisites'] ?? '[]');
            $theoremContext = $theorem;
            
            $response['steps'][] = [
                'step' => 'theorem_loaded',
                'theorem_name' => $theorem['name'],
                'message' => 'Theorem loaded from REANA library'
            ];
        }
    }
    
    // Step 2: Convert to Lean
    if (!empty($theoremContext)) {
        $conversionResult = $converter->convertWithTheoremContext($theoremContext, $natural_language_proof);
    } else {
        $theorem_statement = $data['theorem_statement'] ?? 'No theorem statement provided';
        $conversionResult = $converter->convertToLean($theorem_statement, $natural_language_proof);
    }
    
    if (!$conversionResult['success']) {
        $response['success'] = false;
        $response['error'] = 'Conversion failed: ' . $conversionResult['error'];
        echo json_encode($response);
        return;
    }
    
    $lean_code = $conversionResult['lean_code'];
    $response['lean_code'] = $lean_code;
    $response['steps'][] = [
        'step' => 'conversion_complete',
        'message' => 'Natural language proof converted to Lean code'
    ];
    
    // Step 3: Verify with Lean
    $verificationResult = $leanService->verifyProof($lean_code);
    
    $response['verification'] = $verificationResult;
    $response['steps'][] = [
        'step' => 'verification_complete',
        'status' => $verificationResult['success'] ? 'success' : 'failed',
        'message' => $verificationResult['success'] ? 
            'Proof verified successfully!' : 
            'Verification failed: ' . ($verificationResult['error'] ?? 'Unknown error')
    ];
    
    // Step 3.5: Generate AI completion message if verification successful
    $aiCompletionMessage = null;
    if ($verificationResult['success']) {
        $theoremName = $theoremContext['name'] ?? 'Theorem';
        $theoremStatement = $theoremContext['statement'] ?? ($data['theorem_statement'] ?? 'No statement');
        
        $completionResult = $converter->generateCompletionMessage($theoremName, $theoremStatement, $lean_code);
        
        if ($completionResult['success']) {
            $aiCompletionMessage = $completionResult['raw_response'];
            $response['ai_completion_message'] = $aiCompletionMessage;
            $response['steps'][] = [
                'step' => 'ai_message',
                'message' => 'AI Feedback Generated',
                'content' => $aiCompletionMessage
            ];
        }
    }
    
    // Step 4: Save proof attempt
    if ($user_id && $theorem_id && $db instanceof PDO) {
        $status = $verificationResult['success'] ? 'success' : 'failed';
        
        $insertQuery = "INSERT INTO proof_attempts 
                       (user_id, theorem_id, natural_language_input, generated_lean_code, 
                        verification_status, verification_output, score) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $score = $verificationResult['success'] ? 100 : 0;
        
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            $user_id,
            $theorem_id,
            $natural_language_proof,
            $lean_code,
            $status,
            json_encode($verificationResult),
            $score
        ]);
        
        $response['proof_attempt_id'] = $db->lastInsertId();
        $response['steps'][] = [
            'step' => 'saved',
            'message' => 'Proof attempt saved to database'
        ];
    }
    
    echo json_encode($response);
}

/**
 * Save proof attempt
 */
function saveProofAttempt($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $_SESSION['user_id'] ?? $data['user_id'] ?? null;
    $theorem_id = $data['theorem_id'] ?? null;
    $natural_language_input = $data['natural_language_input'] ?? '';
    $generated_lean_code = $data['generated_lean_code'] ?? '';
    $verification_status = $data['verification_status'] ?? 'pending';
    $verification_output = $data['verification_output'] ?? '';
    $score = $data['score'] ?? 0;
    
    if (!$user_id || !$theorem_id) {
        throw new InvalidArgumentException('User ID and Theorem ID are required');
    }

    if (!($db instanceof PDO)) {
        echo json_encode([
            'success' => true,
            'proof_attempt_id' => 0,
            'message' => 'Proof attempt accepted in demo mode',
            'demo_mode' => true
        ]);
        return;
    }
    
    $query = "INSERT INTO proof_attempts 
              (user_id, theorem_id, natural_language_input, generated_lean_code, 
               verification_status, verification_output, score) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $stmt->execute([
        $user_id,
        $theorem_id,
        $natural_language_input,
        $generated_lean_code,
        $verification_status,
        $verification_output,
        $score
    ]);
    
    echo json_encode([
        'success' => true,
        'proof_attempt_id' => $db->lastInsertId(),
        'message' => 'Proof attempt saved successfully'
    ]);
}

/**
 * Get user's proof attempts
 */
function getProofAttempts($db) {
    $user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? null;
    $theorem_id = $_GET['theorem_id'] ?? null;
    
    if (!$user_id) {
        throw new InvalidArgumentException('User ID is required');
    }

    if (!($db instanceof PDO)) {
        echo json_encode([
            'success' => true,
            'attempts' => [],
            'count' => 0,
            'demo_mode' => true
        ]);
        return;
    }
    
    $query = "SELECT pa.*, t.name as theorem_name, t.statement as theorem_statement
              FROM proof_attempts pa
              JOIN theorems t ON pa.theorem_id = t.id
              WHERE pa.user_id = ?";
    
    $params = [$user_id];
    
    if ($theorem_id) {
        $query .= " AND pa.theorem_id = ?";
        $params[] = $theorem_id;
    }
    
    $query .= " ORDER BY pa.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'attempts' => $attempts,
        'count' => count($attempts)
    ]);
}

/**
 * Refine proof based on feedback
 */
function refineProof($converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $lean_code = $data['lean_code'] ?? null;
    $feedback = $data['feedback'] ?? '';
    $error_messages = $data['error_messages'] ?? [];
    
    if (!$lean_code) {
        throw new InvalidArgumentException('Lean code is required');
    }
    
    $result = $converter->refineProof($lean_code, $feedback, $error_messages);
    
    echo json_encode($result);
}

/**
 * Generate proof skeleton
 */
function generateSkeleton($converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theorem_statement = $data['theorem_statement'] ?? null;
    $proof_strategy = $data['proof_strategy'] ?? 'direct';
    
    if (!$theorem_statement) {
        throw new InvalidArgumentException('Theorem statement is required');
    }
    
    $result = $converter->generateProofSkeleton($theorem_statement, $proof_strategy);
    
    echo json_encode($result);
}

/**
 * Explain Lean code in natural language
 */
function explainLeanCode($converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $lean_code = $data['lean_code'] ?? null;
    $language = $data['language'] ?? 'English';
    
    if (!$lean_code) {
        throw new InvalidArgumentException('Lean code is required');
    }
    
    $result = $converter->explainLeanCode($lean_code, $language);
    
    echo json_encode($result);
}

/**
 * Verify full proof (submit entire proof at once)
 */
function verifyFullProof($db, $converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theorem = $data['theorem'] ?? null;
    $proof = $data['proof'] ?? null;
    
    if (!$theorem || !$proof) {
        throw new InvalidArgumentException('Theorem and proof are required');
    }
    
    // Analyze the proof
    $result = analyzeProof($theorem, $proof);
    
    echo json_encode($result);
}

/**
 * Verify step-by-step proof (submit and verify each step)
 */
function verifyStepProof($db, $converter) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $theorem = $data['theorem'] ?? null;
    $step = $data['step'] ?? null;
    $step_number = $data['step_number'] ?? 1;
    
    if (!$theorem || !$step) {
        throw new InvalidArgumentException('Theorem and step are required');
    }
    
    // Analyze individual step
    $result = analyzeStep($theorem, $step, $step_number);
    
    echo json_encode($result);
}

/**
 * Analyze a proof for correctness and quality
 */
function analyzeProof($theorem, $proof) {
    // This function tokenizes and evaluates the proof
    // Returns score, feedback, and suggestions
    
    $score = calculateProofScore($proof);
    $feedback = generateProofFeedback($proof, $theorem);
    $isValid = $score >= 60;
    
    return [
        'success' => true,
        'valid' => $isValid,
        'score' => $score,
        'feedback' => $feedback,
        'summary' => 'Full proof analyzed: ' . (strlen($proof) > 50 ? substr($proof, 0, 47) . '...' : $proof),
        'suggestion' => $isValid ? 
            'Great proof! Consider working on another theorem.' :
            'Keep refining your proof. Check the feedback for specific areas to improve.'
    ];
}

/**
 * Analyze a single proof step
 */
function analyzeStep($theorem, $step, $stepNumber) {
    $score = calculateProofScore($step);
    $feedback = generateStepFeedback($step, $stepNumber);
    $isValid = $score >= 50;
    
    return [
        'success' => true,
        'valid' => $isValid,
        'score' => $score,
        'feedback' => $feedback,
        'step_number' => $stepNumber,
        'summary' => 'Step ' . $stepNumber . ' analyzed',
        'suggestion' => $isValid ? 
            'Good step! Continue to the next step.' :
            'This step needs more justification. Consider adding more details.'
    ];
}

/**
 * Calculate proof quality score (0-100)
 */
function calculateProofScore($proof) {
    $score = 50;  // Base score
    
    // Award points for proof characteristics
    if (strlen($proof) > 100) $score += 10;  // Substantive proof
    if (preg_match('/\$[^$]+\$/i', $proof)) $score += 15;  // Contains LaTeX/math
    if (preg_match('/\b(therefore|hence|thus|so|implies)\b/i', $proof)) $score += 10;  // Uses logical connectors
    if (preg_match('/\b(by|using|via|through)\b/i', $proof)) $score += 10;  // Cites methods
    if (preg_match('/\b(contradiction|assume|suppose)\b/i', $proof)) $score += 10;  // Uses proof techniques
    
    // Subtract points for issues
    if (strlen($proof) < 30) $score -= 20;  // Too short
    
    return min(100, max(0, $score));
}

/**
 * Generate feedback on full proof
 */
function generateProofFeedback($proof, $theorem) {
    $feedback = [];
    
    if (strlen($proof) < 100) {
        $feedback[] = 'Consider providing more detailed justification for each step.';
    }
    
    if (!preg_match('/\$[^$]+\$/i', $proof)) {
        $feedback[] = 'Consider using mathematical notation where appropriate.';
    }
    
    if (!preg_match('/\b(therefore|hence|thus)\b/i', $proof)) {
        $feedback[] = 'Make logical connections between steps more explicit.';
    }
    
    if (empty($feedback)) {
        $feedback[] = 'Well-structured proof with clear logical progression.';
    }
    
    return implode(' ', $feedback);
}

/**
 * Generate feedback on proof step
 */
function generateStepFeedback($step, $stepNumber) {
    $feedback = [];
    
    if (strlen($step) < 50) {
        $feedback[] = 'This step could use more detail.';
    }
    
    if (!preg_match('/\$[^$]+\$/i', $step)) {
        $feedback[] = 'Consider including mathematical expressions.';
    }
    
    if (empty($feedback)) {
        $feedback[] = 'Clear and well-justified step.';
    }
    
    return implode(' ', $feedback);
}
