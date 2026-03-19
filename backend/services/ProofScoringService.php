<?php
/**
 * Proof Scoring Service
 * Integrates with DeepSeek AI to score mathematical proofs
 * 
 * Scoring Criteria:
 * - Logical Correctness (0-30 points): Proof follows logical steps
 * - Mathematical Rigor (0-25 points): Proper use of theorems and definitions
 * - Clarity & Presentation (0-20 points): Clear explanation and formatting
 * - Completeness (0-15 points): All steps present, no gaps
 * - Efficiency (0-10 points): Elegant/efficient proof approach
 * 
 * Total: 100 points
 */

require_once __DIR__ . '/../config/deepseek.php';

class ProofScoringService {
    private $deepseekApiUrl;
    private $deepseekApiKey;
    private $model;
    
    public function __construct() {
        $config = require __DIR__ . '/../config/deepseek.php';
        $this->deepseekApiUrl = $config['deepseek_api_url'];
        $this->deepseekApiKey = $config['deepseek_api_key'];
        $this->model = $config['model'];
    }
    
    /**
     * Score a proof submission
     * 
     * @param string $theorem The original theorem statement
     * @param string $proof The user's proof in natural language
     * @param string $leanCode Optional: Generated Lean code
     * @return array Scoring result with breakdown
     */
    public function scoreProof($theorem, $proof, $leanCode = null) {
        try {
            $prompt = $this->buildScoringPrompt($theorem, $proof, $leanCode);
            
            $response = $this->callDeepSeekAPI($prompt);
            
            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => $response['error'],
                    'score' => 0
                ];
            }
            
            $scoringResult = $this->parseScoreFromResponse($response['content']);
            
            return [
                'success' => true,
                'total_score' => $scoringResult['total'],
                'breakdown' => $scoringResult['breakdown'],
                'feedback' => $scoringResult['feedback'],
                'strengths' => $scoringResult['strengths'],
                'improvements' => $scoringResult['improvements'],
                'raw_response' => $response['content']
            ];
        } catch (Exception $e) {
            error_log('Proof Scoring Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'score' => 0
            ];
        }
    }
    
    /**
     * Get scoring criteria and weights
     */
    public function getScoringCriteria() {
        return [
            'logical_correctness' => [
                'label' => 'Logical Correctness',
                'weight' => 30,
                'description' => 'Does the proof follow logically sound steps?'
            ],
            'mathematical_rigor' => [
                'label' => 'Mathematical Rigor',
                'weight' => 25,
                'description' => 'Are theorems and definitions properly applied?'
            ],
            'clarity' => [
                'label' => 'Clarity & Presentation',
                'weight' => 20,
                'description' => 'Is the proof clearly explained and well-formatted?'
            ],
            'completeness' => [
                'label' => 'Completeness',
                'weight' => 15,
                'description' => 'Are all necessary steps present?'
            ],
            'efficiency' => [
                'label' => 'Efficiency',
                'weight' => 10,
                'description' => 'Is the approach elegant and efficient?'
            ]
        ];
    }
    
    /**
     * Build the scoring prompt for DeepSeek
     */
    private function buildScoringPrompt($theorem, $proof, $leanCode = null) {
        $criteria = $this->getScoringCriteria();
        
        $criteriaText = "Scoring Criteria (max 100 points):\n";
        foreach ($criteria as $key => $criterion) {
            $criteriaText .= "- {$criterion['label']}: {$criterion['weight']} points - {$criterion['description']}\n";
        }
        
        $systemPrompt = <<<PROMPT
You are an expert mathematician and Real Analysis instructor evaluating student proofs.

$criteriaText

Your task is to:
1. Carefully evaluate the proof against each criterion
2. Assign a score (0-{weight}) for each criterion
3. Provide specific feedback on strengths and areas for improvement
4. Give constructive suggestions for enhancement

Format your response as JSON with this exact structure:
{
  "scores": {
    "logical_correctness": <0-30>,
    "mathematical_rigor": <0-25>,
    "clarity": <0-20>,
    "completeness": <0-15>,
    "efficiency": <0-10>
  },
  "feedback": "<Overall assessment of the proof>",
  "strengths": ["<strength 1>", "<strength 2>", ...],
  "improvements": ["<improvement 1>", "<improvement 2>", ...],
  "detailed_feedback": {
    "logical_correctness": "<feedback for this criterion>",
    "mathematical_rigor": "<feedback for this criterion>",
    "clarity": "<feedback for this criterion>",
    "completeness": "<feedback for this criterion>",
    "efficiency": "<feedback for this criterion>"
  }
}

Be encouraging but honest. Provide specific, actionable feedback.
PROMPT;

        $userMessage = <<<MESSAGE
Please score this proof:

THEOREM: $theorem

PROOF:
$proof
MESSAGE;

        if ($leanCode) {
            $userMessage .= "\n\nFORMAL LEAN CODE:\n$leanCode";
        }
        
        return [
            'system' => $systemPrompt,
            'user' => $userMessage
        ];
    }
    
    /**
     * Call DeepSeek API for scoring
     */
    private function callDeepSeekAPI($prompt) {
        try {
            $payload = [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt['system']
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt['user']
                    ]
                ],
                'temperature' => 0.5,
                'max_tokens' => 2000,
                'response_format' => ['type' => 'json_object']
            ];
            
            $options = [
                'http' => [
                    'header' => [
                        "Content-Type: application/json",
                        "Authorization: Bearer " . $this->deepseekApiKey
                    ],
                    'method' => 'POST',
                    'content' => json_encode($payload),
                    'timeout' => 30
                ]
            ];
            
            $context = stream_context_create($options);
            $response = @file_get_contents($this->deepseekApiUrl, false, $context);
            
            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'Failed to connect to DeepSeek API'
                ];
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'error' => $data['error']['message'] ?? 'API Error'
                ];
            }
            
            if (!isset($data['choices'][0]['message']['content'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid API response format'
                ];
            }
            
            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse and validate scoring response
     */
    private function parseScoreFromResponse($content) {
        try {
            // Try to extract JSON from the response
            $jsonMatch = preg_match('/\{[\s\S]*\}/', $content, $matches);
            if (!$jsonMatch) {
                throw new Exception('No JSON found in response');
            }
            
            $parsed = json_decode($matches[0], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON: ' . json_last_error_msg());
            }
            
            // Validate scores
            if (!isset($parsed['scores'])) {
                throw new Exception('Missing scores object');
            }
            
            $scores = $parsed['scores'];
            $total = $this->calculateTotal($scores);
            
            return [
                'total' => $total,
                'breakdown' => $scores,
                'feedback' => $parsed['feedback'] ?? '',
                'strengths' => $parsed['strengths'] ?? [],
                'improvements' => $parsed['improvements'] ?? [],
                'detailed_feedback' => $parsed['detailed_feedback'] ?? []
            ];
        } catch (Exception $e) {
            error_log('Error parsing score: ' . $e->getMessage());
            // Return a default response
            return [
                'total' => 50,
                'breakdown' => [
                    'logical_correctness' => 15,
                    'mathematical_rigor' => 12,
                    'clarity' => 10,
                    'completeness' => 8,
                    'efficiency' => 5
                ],
                'feedback' => 'Unable to generate detailed scoring. Please review your proof carefully.',
                'strengths' => [],
                'improvements' => []
            ];
        }
    }
    
    /**
     * Calculate total score from breakdown
     */
    private function calculateTotal($scores) {
        $total = 0;
        foreach ($scores as $score) {
            if (is_numeric($score)) {
                $total += $score;
            }
        }
        return min(100, max(0, $total));
    }
}
?>
