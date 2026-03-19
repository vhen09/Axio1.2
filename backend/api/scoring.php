<?php
/**
 * Proof Scoring API Endpoint
 * Handles proof evaluation and scoring
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-initialize database
@require_once __DIR__ . '/../config/auto-setup.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/ProofScoringService.php';

try {
    // Check authentication
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Not authenticated',
            'message' => 'Please log in to score proofs'
        ]);
        exit;
    }
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    $scoringService = new ProofScoringService();
    
    switch ($action) {
        case 'score_proof':
            handleScoreProof($scoringService, $user_id);
            break;
            
        case 'get_criteria':
            handleGetCriteria($scoringService);
            break;
            
        case 'get_history':
            handleGetHistory($user_id);
            break;
            
        case 'get_score':
            handleGetScore($_GET['submission_id'] ?? null, $user_id);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid action',
                'available_actions' => ['score_proof', 'get_criteria', 'get_history', 'get_score']
            ]);
    }
    
} catch (Exception $e) {
    error_log('SCORING API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

/**
 * Score a proof submission
 */
function handleScoreProof($scoringService, $user_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'No data provided'
        ]);
        return;
    }
    
    $theorem = $data['theorem'] ?? '';
    $proof = $data['proof'] ?? '';
    $leanCode = $data['lean_code'] ?? null;
    $submissionId = $data['submission_id'] ?? null;
    
    if (empty($theorem) || empty($proof)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Theorem and proof are required'
        ]);
        return;
    }
    
    // Score the proof
    $result = $scoringService->scoreProof($theorem, $proof, $leanCode);
    
    if ($result['success']) {
        // Save to database if submission ID provided
        if ($submissionId) {
            saveScore($user_id, $submissionId, $result);
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Proof scored successfully',
            'data' => [
                'total_score' => $result['total_score'],
                'breakdown' => $result['breakdown'],
                'feedback' => $result['feedback'],
                'strengths' => $result['strengths'],
                'improvements' => $result['improvements']
            ]
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to score proof'
        ]);
    }
}

/**
 * Get scoring criteria
 */
function handleGetCriteria($scoringService) {
    $criteria = $scoringService->getScoringCriteria();
    
    echo json_encode([
        'success' => true,
        'criteria' => $criteria
    ]);
}

/**
 * Get user's scoring history
 */
function handleGetHistory($user_id) {
    try {
        $db = new Database();
        $connection = $db->getConnection();
        
        if ($db->isDemoMode()) {
            echo json_encode([
                'success' => true,
                'history' => [],
                'message' => 'Demo mode - history unavailable'
            ]);
            return;
        }
        
        $stmt = $connection->prepare("
            SELECT 
                pa.id,
                pa.theorem_id,
                pa.natural_language_input,
                pa.score,
                pa.verification_status,
                pa.created_at,
                t.name as theorem_name
            FROM proof_attempts pa
            LEFT JOIN theorems t ON pa.theorem_id = t.id
            WHERE pa.user_id = ?
            ORDER BY pa.created_at DESC
            LIMIT 50
        ");
        
        $stmt->execute([$user_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'history' => $results,
            'count' => count($results)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Get specific score
 */
function handleGetScore($submissionId, $user_id) {
    if (!$submissionId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Submission ID required'
        ]);
        return;
    }
    
    try {
        $db = new Database();
        $connection = $db->getConnection();
        
        if ($db->isDemoMode()) {
            echo json_encode([
                'success' => true,
                'score' => [
                    'id' => $submissionId,
                    'score' => 85,
                    'message' => 'Demo mode - sample score'
                ]
            ]);
            return;
        }
        
        $stmt = $connection->prepare("
            SELECT 
                pa.id,
                pa.score,
                pa.verification_status,
                pa.verification_output,
                pa.ai_feedback,
                pa.natural_language_input,
                pa.created_at
            FROM proof_attempts pa
            WHERE pa.id = ? AND pa.user_id = ?
            LIMIT 1
        ");
        
        $stmt->execute([$submissionId, $user_id]);
        $score = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$score) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Score not found'
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'score' => $score
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Save score to database
 */
function saveScore($user_id, $submissionId, $scoreData) {
    try {
        $db = new Database();
        $connection = $db->getConnection();
        
        if ($db->isDemoMode()) return;
        
        // Update proof_attempts record with score
        $stmt = $connection->prepare("
            UPDATE proof_attempts 
            SET 
                score = ?,
                ai_feedback = ?,
                verification_status = 'success'
            WHERE id = ? AND user_id = ?
        ");
        
        $feedback = json_encode([
            'breakdown' => $scoreData['breakdown'],
            'feedback' => $scoreData['feedback'],
            'strengths' => $scoreData['strengths'],
            'improvements' => $scoreData['improvements']
        ]);
        
        $stmt->execute([
            $scoreData['total_score'],
            $feedback,
            $submissionId,
            $user_id
        ]);
        
    } catch (Exception $e) {
        error_log('Error saving score: ' . $e->getMessage());
    }
}
?>
