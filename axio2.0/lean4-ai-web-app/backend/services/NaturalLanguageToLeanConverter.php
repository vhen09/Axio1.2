<?php

/**
 * Natural Language to Lean Proof Converter
 * Converts natural language proof descriptions to formal Lean code
 * Supports multiple languages including English, Tagalog, and more
 */

class NaturalLanguageToLeanConverter {
    private $apiUrl;
    private $apiKey;
    private $model;
    
    public function __construct() {
        $config = require __DIR__ . '/../config/deepseek.php';
        $this->apiUrl = $config['deepseek_api_url'];
        $this->apiKey = $config['deepseek_api_key'];
        $this->model = $config['model'];
    }
    
    /**
     * Convert natural language proof to Lean code
     * Accepts any language - English, Tagalog, Spanish, etc.
     */
    public function convertToLean($theorem, $naturalLanguageProof, $theoremContext = []) {
        $systemPrompt = $this->buildSystemPrompt();
        $userMessage = $this->buildConversionRequest($theorem, $naturalLanguageProof, $theoremContext);
        
        return $this->callDeepSeekAPI($systemPrompt, $userMessage);
    }
    
    /**
     * Convert with theorem library context
     */
    public function convertWithTheoremContext($theoremData, $naturalLanguageProof) {
        $context = [
            'lean_template' => $theoremData['lean_code'] ?? '',
            'prerequisites' => $theoremData['prerequisites'] ?? [],
            'hints' => $theoremData['proof_hints'] ?? '',
            'examples' => $theoremData['examples'] ?? ''
        ];
        
        return $this->convertToLean(
            $theoremData['statement'], 
            $naturalLanguageProof, 
            $context
        );
    }
    
    /**
     * Interactive refinement - improve existing Lean code based on feedback
     */
    public function refineProof($leanCode, $feedback, $errorMessages = []) {
        $systemPrompt = "You are an expert in Lean 4 proof assistant. Refine and fix Lean proof code based on feedback and error messages.";
        
        $userMessage = "Current Lean code:\n```lean\n{$leanCode}\n```\n\n";
        
        if (!empty($errorMessages)) {
            $userMessage .= "Error messages:\n";
            foreach ($errorMessages as $error) {
                $userMessage .= "- {$error}\n";
            }
            $userMessage .= "\n";
        }
        
        if ($feedback) {
            $userMessage .= "Feedback: {$feedback}\n\n";
        }
        
        $userMessage .= "Please fix the errors and improve the proof. Provide only the corrected Lean code.";
        
        return $this->callDeepSeekAPI($systemPrompt, $userMessage);
    }
    
    /**
     * Generate proof skeleton from theorem statement
     */
    public function generateProofSkeleton($theoremStatement, $proofStrategy = 'direct') {
        $systemPrompt = "You are a Lean 4 expert. Generate proof skeletons (templates) for theorems.";
        
        $strategies = [
            'direct' => 'Direct proof',
            'contradiction' => 'Proof by contradiction',
            'induction' => 'Proof by induction',
            'contrapositive' => 'Proof by contrapositive',
            'cases' => 'Proof by cases'
        ];
        
        $strategyDescription = $strategies[$proofStrategy] ?? 'Direct proof';
        
        $userMessage = "Theorem: {$theoremStatement}\n\n";
        $userMessage .= "Generate a Lean 4 proof skeleton using {$strategyDescription}. ";
        $userMessage .= "Include all necessary steps with 'sorry' placeholders where actual proof content should go. ";
        $userMessage .= "Add comments explaining what needs to be proven at each step.";
        
        return $this->callDeepSeekAPI($systemPrompt, $userMessage);
    }
    
    /**
     * Explain Lean code in natural language
     */
    public function explainLeanCode($leanCode, $language = 'English') {
        $systemPrompt = "You are a Lean 4 expert who can explain formal proofs in simple, natural language. Respond in {$language}.";
        
        $userMessage = "Explain this Lean proof in {$language}:\n\n```lean\n{$leanCode}\n```\n\n";
        $userMessage .= "Provide a step-by-step explanation that someone learning Real Analysis can understand.";
        
        return $this->callDeepSeekAPI($systemPrompt, $userMessage);
    }
    
    /**
     * Generate AI completion feedback message
     * Called when a proof is successfully verified
     */
    public function generateCompletionMessage($theoremName, $theoremStatement, $leanCode) {
        $systemPrompt = "You are an AI assistant helping mathematicians learn proof writing. You are supportive and celebrate their achievements. Respond ONLY in English.";
        
        $userMessage = "A student has successfully completed a proof for the following theorem:\n\n";
        $userMessage .= "**Theorem**: {$theoremName}\n";
        $userMessage .= "**Statement**: {$theoremStatement}\n\n";
        $userMessage .= "**Lean Proof**:\n```lean\n{$leanCode}\n```\n\n";
        $userMessage .= "Generate a brief, encouraging congratulatory message. IMPORTANT: You MUST explicitly state 'The proof is complete' or similar message. Be warm and supportive, and highlight what they accomplished.";
        
        $result = $this->callDeepSeekAPI($systemPrompt, $userMessage);
        
        // Ensure the message explicitly states completion
        if ($result['success']) {
            $content = $result['raw_response'];
            
            // If the response doesn't contain completion message, add it
            if (!preg_match('/(complete|proven|verified|congratulations)/i', $content)) {
                $result['raw_response'] = "The proof is complete! " . $content;
            }
            
            return $result;
        }
        
        // Fallback message if API fails
        return [
            'success' => true,
            'raw_response' => 'The proof is complete! Excellent work! Your Lean proof has been successfully verified and is mathematically correct. Congratulations on completing this proof!',
            'fallback' => true
        ];
    }
    
    /**
     * Build system prompt for conversion
     */
    private function buildSystemPrompt() {
        return "You are an expert in both Real Analysis and the Lean 4 proof assistant. Your task is to convert natural language proof descriptions into formal Lean 4 code.

IMPORTANT CAPABILITIES:
- Accept proofs in ANY language (English, Tagalog, Spanish, Chinese, etc.)
- Understand informal mathematical reasoning
- Generate syntactically correct Lean 4 code
- Use appropriate tactics and proof strategies
- Import necessary Mathlib libraries
- Follow Lean 4 best practices

CONVERSION GUIDELINES:
1. Parse the natural language proof to understand the logical structure
2. Identify key proof steps and tactics needed
3. Map informal statements to formal Lean syntax
4. Use appropriate tactics: intro, apply, exact, simp, ring, field_simp, etc.
5. Handle quantifiers and logical connectives correctly
6. Import required libraries (Mathlib.Data.Real.Basic, etc.)
7. Provide clear, well-structured code

OUTPUT FORMAT:
- Provide ONLY the Lean code
- Include necessary imports
- Add brief comments for clarity
- Ensure code is syntactically valid
- Use 'sorry' only when the proof step is complex and needs refinement

EXAMPLE:
Natural language (Tagalog): 'Ipakita na ang sum ng dalawang positive numbers ay positive din.'
Lean output:
```lean
import Mathlib.Data.Real.Basic

theorem sum_of_positives {a b : ℝ} (ha : 0 < a) (hb : 0 < b) : 0 < a + b := by
  linarith
```

Be precise, correct, and generate production-ready Lean 4 code.";
    }
    
    /**
     * Build conversion request message
     */
    private function buildConversionRequest($theorem, $naturalLanguageProof, $context) {
        $message = "THEOREM TO PROVE:\n{$theorem}\n\n";
        
        $message .= "NATURAL LANGUAGE PROOF:\n{$naturalLanguageProof}\n\n";
        
        if (!empty($context['lean_template'])) {
            $message .= "THEOREM TEMPLATE:\n```lean\n{$context['lean_template']}\n```\n\n";
        }
        
        if (!empty($context['prerequisites'])) {
            $message .= "AVAILABLE THEOREMS/LEMMAS:\n";
            foreach ($context['prerequisites'] as $prereq) {
                $message .= "- {$prereq}\n";
            }
            $message .= "\n";
        }
        
        if (!empty($context['hints'])) {
            $message .= "HINTS:\n{$context['hints']}\n\n";
        }
        
        $message .= "Convert the natural language proof into formal Lean 4 code. Provide the complete, working Lean proof.";
        
        return $message;
    }
    
    /**
     * Call DeepSeek API
     */
    private function callDeepSeekAPI($systemPrompt, $userMessage) {
        require_once __DIR__ . '/Logger.php';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ],
            'max_tokens' => 3000,
            'temperature' => 0.3  // Lower temperature for more precise code generation
        ];

        Logger::info('NL to Lean Conversion Request');

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            Logger::error('NL to Lean API Error', ['error' => $error]);
            curl_close($ch);
            return [
                'success' => false,
                'error' => 'Conversion failed: ' . $error
            ];
        }
        
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
            $leanCode = $this->extractLeanCode($result['choices'][0]['message']['content']);
            
            Logger::info('NL to Lean Conversion Success');
            return [
                'success' => true,
                'lean_code' => $leanCode,
                'raw_response' => $result['choices'][0]['message']['content'],
                'usage' => $result['usage'] ?? null
            ];
        } else {
            Logger::error('NL to Lean API Error', [
                'http_code' => $httpCode,
                'response' => $result
            ]);
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Conversion failed',
                'raw_response' => $result
            ];
        }
    }
    
    /**
     * Extract Lean code from API response
     */
    private function extractLeanCode($response) {
        // Look for code between ```lean and ``` markers
        if (preg_match('/```lean\s*(.*?)\s*```/s', $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Look for code between ``` and ``` markers
        if (preg_match('/```\s*(.*?)\s*```/s', $response, $matches)) {
            return trim($matches[1]);
        }
        
        // Return the entire response if no code blocks found
        return trim($response);
    }
    
    /**
     * Validate Lean syntax (basic check)
     */
    public function validateSyntax($leanCode) {
        $errors = [];
        
        // Check for basic Lean structure
        if (!preg_match('/theorem|lemma|example|def/', $leanCode)) {
            $errors[] = 'No theorem, lemma, example, or def declaration found';
        }
        
        // Check for proof keyword or tactics
        if (!preg_match('/by\s|:=|proof/', $leanCode)) {
            $errors[] = 'No proof body found';
        }
        
        // Check for balanced braces
        $openBraces = substr_count($leanCode, '{');
        $closeBraces = substr_count($leanCode, '}');
        if ($openBraces !== $closeBraces) {
            $errors[] = 'Unbalanced curly braces';
        }
        
        // Check for balanced parentheses
        $openParens = substr_count($leanCode, '(');
        $closeParens = substr_count($leanCode, ')');
        if ($openParens !== $closeParens) {
            $errors[] = 'Unbalanced parentheses';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
