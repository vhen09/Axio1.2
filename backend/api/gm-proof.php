<?php

/**
 * General Math Proof API Endpoint
 * Supports multi-domain proofs across algebra, calculus, discrete math, geometry, logic, etc.
 * 
 * Usage:
 * POST /backend/api/gm-proof.php
 * 
 * Example requests:
 * {
 *   "action": "get_domain_guidance",
 *   "theorem": "Prove that if x > 0, then x² > 0",
 *   "domain": "algebra"
 * }
 * 
 * {
 *   "action": "get_proof_type_recommendations",
 *   "theorem": "Prove by induction that the sum of first n integers equals n(n+1)/2"
 * }
 */

@require_once __DIR__ . '/../config/auto-setup.php';
require_once __DIR__ . '/../services/GeneralMathProofService.php';
require_once __DIR__ . '/../services/ProofTutorService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8080');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
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

$generalMathService = new GeneralMathProofService();
$tutorService = new ProofTutorService();

try {
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'detect_domain':
            // Detect mathematical domain from statement
            $statement = $input['statement'] ?? $input['theorem'] ?? '';
            if (empty($statement)) {
                throw new Exception('Statement is required');
            }
            
            $domain = $generalMathService->detectMathDomain($statement);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'domain' => $domain,
                'domains' => $generalMathService->getAllDomains()
            ]);
            break;

        case 'detect_proof_type':
            // Detect proof type being used
            $proof = $input['proof'] ?? '';
            if (empty($proof)) {
                throw new Exception('Proof text is required');
            }
            
            $proofType = $generalMathService->detectProofType($proof);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'proofType' => $proofType,
                'proofTypes' => $generalMathService->getAllProofTypes()
            ]);
            break;

        case 'get_domain_guidance':
            // Get domain-specific guidance
            $statement = $input['statement'] ?? $input['theorem'] ?? '';
            $domain = $input['domain'] ?? null;
            
            if (empty($statement)) {
                throw new Exception('Statement is required');
            }
            
            if (!$domain) {
                $domain = $generalMathService->detectMathDomain($statement);
            }
            
            $proofType = $generalMathService->detectProofType($input['proof'] ?? '');
            
            $guidance = $generalMathService->getDomainGuidance($domain, $statement, $proofType);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'guidance' => $guidance
            ]);
            break;

        case 'get_proof_type_recommendations':
            // Get recommended proof types based on statement
            $statement = $input['statement'] ?? $input['theorem'] ?? '';
            if (empty($statement)) {
                throw new Exception('Statement is required');
            }
            
            $recommendations = $generalMathService->getProofTypeRecommendations($statement);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'recommendations' => $recommendations,
                'allTypes' => $generalMathService->getAllProofTypes()
            ]);
            break;

        case 'validate_proof_structure':
            // Validate proof structure based on proof type
            $proof = $input['proof'] ?? '';
            $proofType = $input['proofType'] ?? $generalMathService->detectProofType($proof);
            
            if (empty($proof)) {
                throw new Exception('Proof is required');
            }
            
            $validation = $generalMathService->validateProofStructure($proofType, $proof);
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'validation' => $validation
            ]);
            break;

        case 'get_all_domains':
            // Get all available domains
            $domains = $generalMathService->getAllDomains();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'domains' => $domains
            ]);
            break;

        case 'get_all_proof_types':
            // Get all available proof types
            $proofTypes = $generalMathService->getAllProofTypes();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'proofTypes' => $proofTypes
            ]);
            break;

        case 'get_full_analysis':
            // Get complete analysis of a proof (domain, type, guidance, validation)
            $theorem = $input['theorem'] ?? $input['statement'] ?? '';
            $proof = $input['proof'] ?? '';
            
            if (empty($theorem)) {
                throw new Exception('Theorem is required');
            }
            
            $domain = $generalMathService->detectMathDomain($theorem);
            $proofType = $generalMathService->detectProofType($proof);
            $domainGuidance = $generalMathService->getDomainGuidance($domain, $theorem, $proofType);
            $proofValidation = $generalMathService->validateProofStructure($proofType, $proof);
            $typeRecommendations = $generalMathService->getProofTypeRecommendations($theorem);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'analysis' => [
                    'detectedDomain' => $domain,
                    'detectedProofType' => $proofType,
                    'domainGuidance' => $domainGuidance,
                    'proofValidation' => $proofValidation,
                    'typeRecommendations' => $typeRecommendations
                ]
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Unknown action: ' . $action,
                'availableActions' => [
                    'detect_domain',
                    'detect_proof_type',
                    'get_domain_guidance',
                    'get_proof_type_recommendations',
                    'validate_proof_structure',
                    'get_all_domains',
                    'get_all_proof_types',
                    'get_full_analysis'
                ]
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
