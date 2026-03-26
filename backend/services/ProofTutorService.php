<?php

/**
 * ProofTutorService - AI-powered tutoring system for Real Analysis proof assistance
 * Uses DeepSeek API to provide step-by-step guidance, verification, and feedback
 */
class ProofTutorService {
    private $apiUrl;
    private $apiKey;
    private $model;
    private $maxTokens;
    private $temperature;
    private $timeout;
    private $connectTimeout;
    private $cacheTtl;
    private $retryAttempts;
    private $retryDelayMs;
    private $fallbackOnTimeout;

    public function __construct() {
        $config = require __DIR__ . '/../config/deepseek.php';
        $this->apiUrl = $config['deepseek_api_url'];
        $this->apiKey = $config['deepseek_api_key'];
        $this->model = $config['model'];
        $this->maxTokens = $config['max_tokens'];
        $this->temperature = $config['temperature'];
        $this->timeout = $config['timeout'] ?? 35;
        $this->connectTimeout = $config['connect_timeout'] ?? 8;
        $this->cacheTtl = $config['cache_ttl'] ?? 120;
        $this->retryAttempts = max(1, (int)($config['retry_attempts'] ?? 1));
        $this->retryDelayMs = max(0, (int)($config['retry_delay_ms'] ?? 0));
        $this->fallbackOnTimeout = (bool)($config['fallback_on_timeout'] ?? false);
    }

    /**
     * Get AI tutoring assistance for theorem proving
     */
    public function getTutoringAssistance($theorem, $currentProof = '', $context = []) {
        $systemPrompt = $this->buildSystemPrompt() . "\n\n" . $this->buildTheoremConstraintRules();
        $userMessage = $this->buildUserMessage($theorem, $currentProof, $context);

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 900,
            'fallback_type' => 'assistance',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Provide step-by-step proof guidance
     */
    public function getStepByStepGuidance($theorem, $currentStep = 0, $previousSteps = []) {
        $systemPrompt = "You are an expert Real Analysis professor providing detailed proof guidance. For EACH step you provide:

**REQUIRED FORMAT:**
## Next Step: Step " . ($currentStep + 1) . "

### What to Write:
[The exact mathematical statement for this step using proper notation: ∀, ∃, ∈, ε, δ, etc.]

### Why This Step:
[Detailed explanation of why this follows logically - minimum 3 sentences]

### Justification:
[Cite specific: Definition of [X], Theorem [Y], Previous Step [Z], Algebraic manipulation, etc.]

### Detailed Work:
[Show ALL intermediate calculations, algebraic steps, or logical inferences. Don't skip anything.]

### How It Connects:
[Explain how this step moves us toward the goal]

### What Comes Next:
[Brief hint about the next logical move]

**REAL ANALYSIS STANDARDS:**
- Use formal ε-δ notation for limits
- Show inequality manipulations step-by-step
- Apply Triangle Inequality explicitly: |a+b| ≤ |a| + |b|
- Work backwards when finding δ in terms of ε
- Use proper quantifier order: ∀ε>0, ∃δ>0
- Define all variables and their domains

Provide substantial mathematical detail (minimum 120 words per step).\n\n" . $this->buildTheoremConstraintRules();
        
        $userMessage = $this->buildAuthoritativeTheoremSection($theorem) . "\n\n";
        
        if (!empty($previousSteps)) {
            $userMessage .= "Previous steps completed:\n";
            foreach ($previousSteps as $i => $step) {
                $userMessage .= ($i + 1) . ". {$step}\n";
            }
            $userMessage .= "\nWhat should be the next step (step " . ($currentStep + 1) . ")? Provide detailed reasoning and any relevant definitions or lemmas needed.";
        } else {
            $userMessage .= "How should I begin this proof? What's the first step and why? Provide complete detail.";
        }

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 800,
            'fallback_type' => 'step_by_step',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Verify a proof step and provide feedback
     * IMPROVED: Detects when proof is complete and adjusts response accordingly
     */
    public function verifyProofStep($theorem, $step, $stepNumber, $previousSteps = []) {
        // Build complete proof context to assess completeness
        $allSteps = array_merge($previousSteps, [$step]);
        $allStepsText = implode("\n", array_map(function($s, $i) {
            return ($i + 1) . ". " . $s;
        }, $allSteps, array_keys($allSteps)));
        
        // Build improved system prompt that detects proof completion
        $systemPrompt = "You are a rigorous Real Analysis proof verifier. Your task is to:
1. Evaluate if the current step is logically correct and properly justified
2. Assess whether the proof (all steps combined) is now COMPLETE and rigorous
3. Format your response with the required sections

CRITICAL INSTRUCTION - YOU MUST DO THIS:
- After evaluating correctness, you MUST assess if the proof is complete
- A proof is COMPLETE if it fully proves the theorem statement without gaps or missing steps
- Do NOT ignore this assessment - it's the MOST IMPORTANT part of your response

Your response MUST include these sections ALWAYS:

Status:
✅ Correct (if the step is logically valid and properly justified)
⚠️ Needs Work (if incomplete, informal, or not fully justified)
❌ Incorrect (if mathematically wrong)

Input Summary:
Briefly describe what the student did (1–2 sentences only).

What's Wrong / Missing (ONLY if not fully correct):
Clearly state what is incorrect, missing, or informal.
Be concise and focus only on important mathematical issues.

Improvement:
Briefly state how the step can be improved or corrected.
Do not rewrite the full proof.

PROOF COMPLETION ASSESSMENT:
After checking the current step, you MUST assess whether all steps together form a COMPLETE proof.
Write EXACTLY ONE of these outcomes:
- ✅ PROOF IS COMPLETE AND CORRECT: The theorem is now fully proven. No further steps needed.
- ⚠️ PROOF IS INCOMPLETE: More steps are needed. [Describe what's missing]
- ❌ PROOF HAS ISSUES: The proof cannot be completed as-is due to these problems [describe]

Next Step Guidance (ONLY if proof is NOT complete):
If the proof is incomplete, give a short hint on what should be done next.
If the proof IS complete, write: \"No further steps needed - the proof is complete.\"
Do NOT give the full solution or carry out the next proof step.

⚠️ Important Rules:
- Keep the response short and focused.
- Do NOT include long explanations.
- Do NOT restate the entire proof.
- Do NOT solve the next step.
- Be clear, direct, and structured.
- ALWAYS include the PROOF COMPLETION ASSESSMENT section.

" . $this->buildTheoremConstraintRules();
        
        $context = $this->buildAuthoritativeTheoremSection($theorem) . "\n\n";
        $context .= "Complete proof progress so far:\n{$allStepsText}\n\n";
        
        $userMessage = $context . "Is Step {$stepNumber} logically correct? After reviewing all steps together, is the proof now complete? Provide your full assessment including the PROOF COMPLETION ASSESSMENT section.";

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 800,
            'fallback_type' => 'verify',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Explain Real Analysis concepts and definitions
     */
    public function explainConcept($concept, $context = '') {
        $systemPrompt = "You are a Real Analysis expert. Explain mathematical concepts clearly with formal definitions, intuitive explanations, and relevant examples.";
        
        $userMessage = "Please explain: {$concept}";
        if ($context) {
            $userMessage .= "\n\nContext: {$context}";
        }

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 700,
            'fallback_type' => 'assistance',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Suggest proof strategies for a given theorem
     */
    public function suggestProofStrategy($theorem, $theoremType = '') {
        $systemPrompt = "You are a Real Analysis expert in proof methodology. Provide a COMPREHENSIVE proof strategy with this structure:

## 1. Understanding the Theorem

### Mathematical Statement:
[Rewrite theorem with proper notation: ∀, ∃, ∈, →, etc.]

### What It Means:
[Intuitive explanation in plain language]

### Key Components:
- **Given/Assumptions:** [List all]
- **To Prove:** [State goal clearly]
- **Definitions Needed:** [List formal definitions required]

## 2. Choosing a Proof Technique

Analyze which method is best:

**Option A: Direct Proof**
- Applicability: [When to use]
- Approach: [How it works for this theorem]
- Pros/Cons: [For this specific case]

**Option B: Proof by Contradiction**
- Assume: [What to negate]
- Goal: [What contradiction to derive]
- Best if: [Conditions]

**Option C: [Other relevant methods: Contrapositive, Induction, ε-δ, etc.]**

**RECOMMENDED:** [Choose one and explain why in detail]

## 3. Relevant Theorems & Definitions

List everything you'll need:
- **Definition 1:** [Formal statement]
- **Theorem/Lemma 1:** [Name and statement]
- **Key Inequalities:** Triangle Inequality, etc.

## 4. Detailed Proof Outline

### Step 1: [Major step]
*What to do:* [Specific actions]
*Why:* [Reasoning]
*Watch out for:* [Potential pitfalls]

### Step 2: [Next major step]
[Same detail...]

[Continue for 4-6 major steps]

## 5. Common Pitfalls
- [List 2-3 mistakes students make on this type of proof]

## 6. Verification Checklist
- [ ] All quantifiers in correct order
- [ ] All variables defined
- [ ] Each step justified
- [ ] Edge cases considered

Be thorough and pedagogical. Minimum 250 words with substantial mathematical content.\n\n" . $this->buildTheoremConstraintRules();
        
        $userMessage = $this->buildAuthoritativeTheoremSection($theorem) . "\n";
        if ($theoremType) {
            $userMessage .= "Type: {$theoremType}\n";
        }
        $userMessage .= "\nWhat proof strategy or technique should I use? Explain the approach and outline the key steps in complete detail.";

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 900,
            'fallback_type' => 'assistance',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Check complete proof for correctness
     */
    public function checkCompleteProof($theorem, $proof) {
        $systemPrompt = "You are a rigorous Real Analysis proof verifier. Analyze the entire proof for logical correctness, completeness, and rigor. Identify any gaps, errors, or areas for improvement.

    REQUIRED OUTPUT:
    ## Statement Check: ✅ MATCH / ❌ MISMATCH
    ## Proof Verdict: ✓ CORRECT / ⚠ NEEDS WORK / ✗ INCORRECT
    ## Main Issues
    ## Correction Guide for Student (numbered, concrete, actionable)
    ## Suggested Corrected Outline (3-6 steps)

    If theorem statement is mismatched, prioritize correction of theorem statement first before proof details.\n\n" . $this->buildTheoremConstraintRules();
        
        $userMessage = $this->buildAuthoritativeTheoremSection($theorem) . "\n\nProof:\n{$proof}\n\nPlease provide a comprehensive review of this proof. Is it correct and complete?";

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 800,
            'fallback_type' => 'verify',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Get hints for stuck students
     */
    public function getHint($theorem, $currentProof, $stuckPoint) {
        $systemPrompt = "You are a helpful Real Analysis tutor. When students are stuck, provide hints that guide them without giving away the complete solution. Ask leading questions and suggest things to consider.\n\n" . $this->buildTheoremConstraintRules();
        
        $userMessage = $this->buildAuthoritativeTheoremSection($theorem) . "\n\nCurrent work:\n{$currentProof}\n\nI'm stuck at: {$stuckPoint}\n\nCan you give me a hint to help me proceed?";

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 650,
            'fallback_type' => 'assistance',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Suggest similar theorems and exercises
     */
    public function suggestRelatedTheorems($theorem, $difficulty = 'similar') {
        $systemPrompt = "You are a Real Analysis curriculum expert. Suggest related theorems and exercises that help build understanding progressively.";
        
        $userMessage = "I just worked on: {$theorem}\n\nSuggest {$difficulty} theorems or exercises that would help reinforce this concept or build upon it.";

        return $this->callDeepSeekAPI($systemPrompt, $userMessage, [
            'max_tokens' => 700,
            'fallback_type' => 'assistance',
            'cache_ttl' => $this->cacheTtl
        ]);
    }

    /**
     * Get demo mode response when API is unavailable
     */
    private function getDemoResponse($type, $theorem, $currentProof = '') {
        $responses = [
            'assistance' => [
                'success' => true,
                'response' => "**🎯 REANA Proof Assistant**\n\n" .
                    "**Theorem: \"$theorem\"**\n\n" .
                    "### 🔍 Step 1: Understanding the Problem\n" .
                    "Break down what you're given and what you need to prove.\n\n" .
                    "**Given Conditions:**\n" .
                    "- Identify all assumptions\n" .
                    "- List relevant definitions\n" .
                    "- Note any constraints\n\n" .
                    "**To Prove:**\n" .
                    "- State the conclusion clearly\n" .
                    "- Identify what makes this true\n\n" .
                    "### 💡 Step 2: Choose Your Strategy\n\n" .
                    "**Common Proof Techniques:**\n" .
                    "1. **Direct Proof** - Show P → Q directly\n" .
                    "2. **Contradiction** - Assume ¬Q and derive contradiction\n" .
                    "3. **Contrapositive** - Prove ¬Q → ¬P\n" .
                    "4. **Induction** - For sequences and series\n" .
                    "5. **Epsilon-Delta** - For limits and continuity\n\n" .
                    "### 🛠️ Step 3: Build Your Proof\n\n" .
                    "**Structure:**\n" .
                    "```\n" .
                    "Proof:\n" .
                    "  Let [define variables]\n" .
                    "  Assume [given conditions]\n" .
                    "  [Logical steps with justification]\n" .
                    "  Therefore, [conclusion]\n" .
                    "  ■\n" .
                    "```\n\n" .
                    "### ✅ Step 4: Verify Your Work\n" .
                    "- Check each logical step\n" .
                    "- Ensure proper use of definitions\n" .
                    "- Verify all cases covered\n" .
                    "- Confirm conclusion follows\n\n" .
                    "### 📚 Real Analysis Tips\n\n" .
                    "**For Limits & Continuity:**\n" .
                    "- Use ε-δ definition\n" .
                    "- Apply triangle inequality\n" .
                    "- Work backwards from ε to find δ\n\n" .
                    "**For Sequences:**\n" .
                    "- Check if monotone and bounded\n" .
                    "- Use Cauchy criterion\n" .
                    "- Apply limit laws\n\n" .
                    "**For Series:**\n" .
                    "- Comparison test\n" .
                    "- Ratio test\n" .
                    "- Root test\n\n" .
                    "---\n" .
                    "**Good luck with your proof! 🚀**",
                'demo_mode' => false
            ],
            'step_by_step' => [
                'success' => true,
                'response' => "**📝 Step-by-Step Proof Guidance**\n\n" .
                    "**Theorem: \"$theorem\"**\n\n" .
                    "### Step 1: Set Up\n" .
                    "Start by writing \"Proof:\" and state all given information clearly.\n\n" .
                    "### Step 2: Apply Definitions\n" .
                    "Write out the formal definitions of all key terms in the theorem.\n\n" .
                    "### Step 3: Develop Logic\n" .
                    "Use the definitions to build logical connections between givens and goal.\n\n" .
                    "### Step 4: Conclude\n" .
                    "State your conclusion clearly and end with ■\n\n" .
                    "**Remember:** Each step should follow logically from the previous!",
                'demo_mode' => false
            ],
            'verify' => [
                'success' => true,
                'response' => "**✅ Proof Verification Checklist**\n\n" .
                    "**Structure Check:**\n" .
                    "✓ Proof starts with 'Proof:'\n" .
                    "✓ Variables are defined\n" .
                    "✓ Logical flow is present\n\n" .
                    "**Logic Review:**\n" .
                    "Check that each step follows from previous ones.\n\n" .
                    "**Suggestions for Improvement:**\n" .
                    "- Add more detailed justification\n" .
                    "- Reference specific theorems used\n" .
                    "- Clarify key transitions\n" .
                    "- Ensure all cases are covered",
                'demo_mode' => false
            ]
        ];
        
        return $responses[$type] ?? [
            'success' => true,
            'response' => "**📚 Proof Guidance**\n\n" .
                         "Focus on these key steps:\n\n" .
                         "1. **Understand** - Read and break down the theorem\n" .
                         "2. **Plan** - Choose your proof strategy\n" .
                         "3. **Execute** - Build logical steps\n" .
                         "4. **Verify** - Check your work\n\n" .
                         "Good luck with your proof!",
            'demo_mode' => false
        ];
    }
    
    /**
     * Build system prompt for general tutoring
     */
    private function buildSystemPrompt() {
        return "You are an expert Real Analysis professor specializing in rigorous mathematical proofs. Your responses MUST follow these guidelines:

**CRITICAL: ALWAYS USE ACTUAL MATHEMATICAL SYMBOLS - NEVER USE TEXT ALTERNATIVES**
- ALWAYS use Unicode symbols: ∀ (not 'for all'), ∃ (not 'exists'), ∈ (not 'in'), ε (not 'epsilon'), δ (not 'delta')
- REQUIRED symbols: ∀, ∃, ∈, ∉, ⊂, ⊆, ∪, ∩, →, ⇒, ⇔, ¬, ≤, ≥, ≠, ≈, ∞, ±, ×, ÷, √, ∑, ∏, ∫, ∂, ℕ, ℤ, ℚ, ℝ, ℂ, ∅
- Greek letters: α, β, γ, δ, ε, θ, λ, μ, π, σ, Δ, Σ
- Subscripts/superscripts: Use Unicode when available (aₙ, x², etc.)
- Write '|x|' for absolute value, NOT 'abs(x)'
- Write 'lim(n→∞)' or 'lim_{n→∞}', NOT 'limit as n approaches infinity'

**FORMATTING REQUIREMENTS:**
- Structure with clear headings (## for main sections, ### for subsections)
- Number all proof steps explicitly (Step 1:, Step 2:, etc.)
- Use bullet points for lists and sub-points
- Include formal definitions in proper mathematical notation with actual symbols
- Show ALL intermediate steps - never skip algebraic or logical steps

**CONTENT REQUIREMENTS:**

1. **Understanding Phase**
   - Restate the theorem in clear mathematical notation
   - Identify: Given conditions, To Prove statement, Key definitions needed
   - Explain what the theorem means intuitively

2. **Proof Strategy**
   - Name the technique: Direct proof, Contradiction, Contrapositive, Induction, ε-δ, etc.
   - Explain WHY this technique is appropriate
   - Outline the major logical steps (3-5 main steps)

3. **Formal Proof Structure**
   Always use this format:
   
   **Given:** [List all assumptions with proper notation]
   **To Prove:** [State goal formally]
   
   **Proof:**
   
   Step 1: [Clear mathematical statement]
     *Justification:* [Cite definition/theorem/previous step]
     *Work:* [Show calculations if applicable]
   
   Step 2: [Next logical statement]
     *Justification:* [Why this follows]
     *Work:* [Algebraic manipulations]
   
   [Continue with all steps...]
   
   **Therefore**, [state conclusion]. ■

4. **Real Analysis Standards**
   - Limits: Use formal ε-δ definition: ∀ε>0, ∃δ>0 such that |x-a|<δ ⇒ |f(x)-L|<ε
   - Sequences: Use aₙ notation, specify n∈ℕ or n≥N
   - Convergence: Show |aₙ-L|<ε for all n>N
   - Continuity: Prove with ε-δ or sequential criterion
   - Triangle Inequality: |a+b| ≤ |a| + |b|
   - Always specify domains: x∈ℝ, S⊆ℝ, etc.

5. **Reference Theorems**
   Cite by name: Triangle Inequality, Archimedean Property, Squeeze Theorem, 
   Bolzano-Weierstrass, Monotone Convergence, Intermediate Value Theorem, etc.

6. **Thoroughness**
   - Minimum 200 words for strategy suggestions
   - Minimum 150 words for step-by-step guidance
   - Show intermediate calculations
   - Explain every logical inference
   - Check edge cases

Be rigorous, detailed, and educational. Build both formal understanding AND intuition.";
    }

    private function buildTheoremConstraintRules() {
        return "THEOREM SCOPE RULES (MANDATORY):
- Use ONLY the exact theorem statement provided by the user as source of truth.
- Do NOT add extra assumptions, variables, domains, or goals not explicitly in the theorem statement.
- If student text changes theorem meaning, label it as statement mismatch and correct it.
- All guidance must stay anchored to the exact theorem statement.
- When proof is incorrect, provide a concise correction guide with concrete next actions.";
    }

    private function buildAuthoritativeTheoremSection($theorem) {
        $safeTheorem = trim((string)$theorem);
        return "Authoritative Theorem Statement (ONLY source of truth):\n\"{$safeTheorem}\"";
    }

    /**
     * Build user message with context
     */
    private function buildUserMessage($theorem, $currentProof, $context) {
        $message = $this->buildAuthoritativeTheoremSection($theorem) . "\n\n";
        
        if ($currentProof) {
            $message .= "Current proof attempt:\n{$currentProof}\n\n";
        }
        
        if (!empty($context)) {
            if (isset($context['question'])) {
                $message .= "Question: {$context['question']}\n";
            }
            if (isset($context['difficulty'])) {
                $message .= "Student level: {$context['difficulty']}\n";
            }
        }
        
        if (!$currentProof) {
            $message .= "How should I approach proving this theorem? What's the overall strategy?";
        } else {
            $message .= "Please review my proof and provide feedback. Am I on the right track?";
        }
        
        return $message;
    }

    /**
     * Call DeepSeek API
     */
    private function callDeepSeekAPI($systemPrompt, $userMessage, $options = []) {
        require_once __DIR__ . '/Logger.php';

        $effectiveMaxTokens = $options['max_tokens'] ?? $this->maxTokens;
        $effectiveTemperature = $options['temperature'] ?? $this->temperature;
        $cacheTtl = $options['cache_ttl'] ?? $this->cacheTtl;
        $fallbackType = $options['fallback_type'] ?? 'assistance';

        $cacheKey = $this->buildCacheKey($systemPrompt, $userMessage, $effectiveMaxTokens, $effectiveTemperature);
        $cached = $this->getCachedResponse($cacheKey, $cacheTtl);
        if ($cached !== null) {
            $cached['cached'] = true;
            return $cached;
        }
        
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
            'max_tokens' => $effectiveMaxTokens,
            'temperature' => $effectiveTemperature
        ];

        $attempt = 0;
        $lastError = 'AI service error. Please try again.';

        while ($attempt < $this->retryAttempts) {
            $attempt++;
            $startedAt = microtime(true);
            Logger::info('DeepSeek API Request', [
                'model' => $this->model,
                'max_tokens' => $effectiveMaxTokens,
                'attempt' => $attempt
            ]);

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($errno) {
                Logger::error('DeepSeek API cURL Error', ['errno' => $errno, 'error' => $error, 'attempt' => $attempt]);

                if ($errno === 28 && $attempt < $this->retryAttempts) {
                    if ($this->retryDelayMs > 0) {
                        usleep($this->retryDelayMs * 1000);
                    }
                    continue;
                }

                if ($errno === 28 && $this->fallbackOnTimeout) {
                    return $this->buildFallbackResponse($fallbackType, $userMessage, 'Network timeout, using fast fallback guidance.');
                }

                $lastError = $errno === 28
                    ? 'DeepSeek request timed out. Please retry; no fallback response was used.'
                    : 'Temporary AI connection issue. Please try again.';
                break;
            }

            $result = json_decode($response, true);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
                $normalizedResponse = $this->normalizeMathSymbols($result['choices'][0]['message']['content']);
                Logger::info('DeepSeek API Success', [
                    'duration_ms' => $durationMs,
                    'tokens' => $result['usage'] ?? 'N/A',
                    'attempt' => $attempt
                ]);

                $payload = [
                    'success' => true,
                    'response' => $normalizedResponse,
                    'usage' => $result['usage'] ?? null
                ];

                $this->setCachedResponse($cacheKey, $payload);

                return $payload;
            }

            if (($httpCode === 408 || $httpCode === 429 || $httpCode >= 500) && $attempt < $this->retryAttempts) {
                if ($this->retryDelayMs > 0) {
                    usleep($this->retryDelayMs * 1000);
                }
                continue;
            }

            if ($httpCode === 401) {
                return [
                    'success' => false,
                    'error' => 'DeepSeek API key is invalid. Update deepseek_api_key in backend/config/deepseek.php.'
                ];
            }

            if ($httpCode === 402) {
                return [
                    'success' => false,
                    'error' => 'DeepSeek account has insufficient balance. Top up credits to continue live AI responses.'
                ];
            }

            if (($httpCode === 408 || $httpCode === 429 || $httpCode >= 500) && $this->fallbackOnTimeout) {
                return $this->buildFallbackResponse($fallbackType, $userMessage, 'DeepSeek is busy right now, using fallback guidance.');
            }

            Logger::error('DeepSeek API Error', [
                'http_code' => $httpCode,
                'duration_ms' => $durationMs,
                'attempt' => $attempt,
                'response' => $result
            ]);

            $lastError = $result['error']['message'] ?? 'AI service error. Please try again.';
            break;
        }

        return [
            'success' => false,
            'error' => $lastError
        ];
    }

    /**
     * Create conversation history for multi-turn tutoring
     */
    public function continueConversation($messages) {
        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return [
                'success' => false,
                'error' => 'API request failed: ' . curl_error($ch)
            ];
        }
        
        curl_close($ch);

        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['choices'][0]['message']['content'])) {
            $normalizedResponse = $this->normalizeMathSymbols($result['choices'][0]['message']['content']);
            return [
                'success' => true,
                'response' => $normalizedResponse,
                'usage' => $result['usage'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown API error'
            ];
        }
    }

    private function buildCacheKey($systemPrompt, $userMessage, $maxTokens, $temperature) {
        return hash('sha256', implode('||', [
            $this->model,
            (string) $maxTokens,
            (string) $temperature,
            $systemPrompt,
            $userMessage
        ]));
    }

    private function getCacheFilePath($cacheKey) {
        return __DIR__ . '/../logs/cache_' . $cacheKey . '.json';
    }

    private function getCachedResponse($cacheKey, $cacheTtl) {
        if ($cacheTtl <= 0) {
            return null;
        }

        $path = $this->getCacheFilePath($cacheKey);
        if (!file_exists($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload) || !isset($payload['timestamp'], $payload['data'])) {
            return null;
        }

        if ((time() - (int) $payload['timestamp']) > $cacheTtl) {
            @unlink($path);
            return null;
        }

        return $payload['data'];
    }

    private function setCachedResponse($cacheKey, $data) {
        $path = $this->getCacheFilePath($cacheKey);
        $payload = [
            'timestamp' => time(),
            'data' => $data
        ];

        @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    private function normalizeMathSymbols($text) {
        if (!is_string($text) || $text === '') {
            return $text;
        }

        $replacements = [
            '/\\\\forall\b/u' => '∀',
            '/\\\\exists\b/u' => '∃',
            '/\\\\in\b/u' => '∈',
            '/\\\\notin\b/u' => '∉',
            '/\\\\subseteq\b/u' => '⊆',
            '/\\\\subset\b/u' => '⊂',
            '/\\\\cup\b/u' => '∪',
            '/\\\\cap\b/u' => '∩',
            '/\\\\to\b/u' => '→',
            '/\\\\Rightarrow\b/u' => '⇒',
            '/\\\\Leftrightarrow\b/u' => '⇔',
            '/\\\\neg\b/u' => '¬',
            '/\\\\leq\b/u' => '≤',
            '/\\\\geq\b/u' => '≥',
            '/\\\\neq\b/u' => '≠',
            '/\\\\approx\b/u' => '≈',
            '/\\\\infty\b/u' => '∞',
            '/\\\\times\b/u' => '×',
            '/\\\\div\b/u' => '÷',
            '/\\\\sqrt\b/u' => '√',
            '/\\\\sum\b/u' => '∑',
            '/\\\\prod\b/u' => '∏',
            '/\\\\int\b/u' => '∫',
            '/\\\\partial\b/u' => '∂',
            '/\\\\epsilon\b/u' => 'ε',
            '/\\\\delta\b/u' => 'δ',
            '/\\\\alpha\b/u' => 'α',
            '/\\\\beta\b/u' => 'β',
            '/\\\\gamma\b/u' => 'γ',
            '/\\\\theta\b/u' => 'θ',
            '/\\\\lambda\b/u' => 'λ',
            '/\\\\mu\b/u' => 'μ',
            '/\\\\pi\b/u' => 'π',
            '/\\\\sigma\b/u' => 'σ',
            '/\\\\mathbb\{N\}/u' => 'ℕ',
            '/\\\\mathbb\{Z\}/u' => 'ℤ',
            '/\\\\mathbb\{Q\}/u' => 'ℚ',
            '/\\\\mathbb\{R\}/u' => 'ℝ',
            '/\\\\mathbb\{C\}/u' => 'ℂ',
            '/\\\\emptyset\b/u' => '∅',
            '/\bfor all\b/iu' => '∀',
            '/\bthere exists\b/iu' => '∃',
            '/\bbelongs to\b/iu' => '∈',
            '/\bnot belongs to\b/iu' => '∉',
            '/->/u' => '→',
            '/=>/u' => '⇒'
        ];

        $normalized = $text;
        foreach ($replacements as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized);
        }

        return $normalized;
    }

    private function buildFallbackResponse($type, $userMessage, $reason = '') {
        $theorem = $this->extractTheoremFromMessage($userMessage);
        $fallback = $this->getDemoResponse($type, $theorem);

        if (!isset($fallback['response'])) {
            $fallback['response'] = 'Use formal notation: ∀ε>0, ∃δ>0, x∈ℝ, and conclude logically step-by-step.';
        }

        $fallback['response'] = $this->normalizeMathSymbols($fallback['response']);
        $fallback['fallback'] = true;
        if ($reason !== '') {
            $fallback['notice'] = $reason;
        }

        return $fallback;
    }

    private function extractTheoremFromMessage($userMessage) {
        if (!is_string($userMessage)) {
            return 'Real Analysis theorem';
        }

        if (preg_match('/Theorem:\s*(.+)/u', $userMessage, $matches)) {
            $line = trim(explode("\n", $matches[1])[0]);
            return $line !== '' ? $line : 'Real Analysis theorem';
        }

        return 'Real Analysis theorem';
    }
}
