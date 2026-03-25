<?php

/**
 * GeneralMathProofService - Expanded proof tutoring for all mathematics domains
 * Supports multiple proof types, math domains, and advanced feedback
 * 
 * Domains Supported:
 * - Algebra (equations, systems, polynomials, matrices)
 * - Calculus (limits, derivatives, integrals, sequences, series)
 * - Discrete Mathematics (combinatorics, graphs, number theory)
 * - Geometry (Euclidean, coordinate, transformations)
 * - Logic (propositional, predicate, set theory)
 * - Real Analysis (limits, continuity, differentiability, integrals)
 * - Linear Algebra (vectors, matrices, eigenvalues, transformations)
 * - Probability & Statistics (distributions, statistical inference)
 */

class GeneralMathProofService {
    private $deepseekService;
    private $proofTypes;
    private $mathDomains;

    public function __construct() {
        $this->initializeProofTypes();
        $this->initializeMathDomains();
    }

    /**
     * Initialize supported proof types
     */
    private function initializeProofTypes() {
        $this->proofTypes = [
            'direct' => [
                'name' => 'Direct Proof',
                'description' => 'Assume premises and derive conclusion through logical steps',
                'keywords' => ['assume', 'suppose', 'let', 'then', 'therefore'],
                'structure' => 'Linear forward derivation from assumptions to conclusion'
            ],
            'contradiction' => [
                'name' => 'Proof by Contradiction',
                'description' => 'Assume the negation and derive a contradiction',
                'keywords' => ['assume not', 'contradict', 'contradiction', 'suppose'],
                'structure' => 'Assume ~P, derive false statement, conclude P'
            ],
            'induction' => [
                'name' => 'Mathematical Induction',
                'description' => 'Prove base case, then inductive step',
                'keywords' => ['base case', 'inductive step', 'assume', 'show', 'induction hypothesis'],
                'structure' => 'P(0) ∧ (P(n) → P(n+1)) ⟹ ∀n P(n)'
            ],
            'cases' => [
                'name' => 'Proof by Cases',
                'description' => 'Partition the domain and prove each case',
                'keywords' => ['case', 'if', 'when', 'then', 'in all cases'],
                'structure' => 'P ∨ Q, P ⟹ R, Q ⟹ R, therefore R'
            ],
            'construction' => [
                'name' => 'Constructive Proof',
                'description' => 'Construct the object whose existence is claimed',
                'keywords' => ['construct', 'define', 'let', 'consider', 'define'],
                'structure' => 'Explicit construction of object with required properties'
            ],
            'counterexample' => [
                'name' => 'Disproof by Counterexample',
                'description' => 'Find an example that violates the claim',
                'keywords' => ['counterexample', 'consider', 'for instance', 'but', 'contradiction'],
                'structure' => '¬∀x P(x) ≡ ∃x ¬P(x)'
            ],
            'contrapositive' => [
                'name' => 'Proof by Contrapositive',
                'description' => 'Prove ¬Q → ¬P instead of P → Q',
                'keywords' => ['contrapositive', 'contrapositive', 'instead', 'equivalently'],
                'structure' => 'P → Q ≡ ¬Q → ¬P'
            ],
            'algorithm' => [
                'name' => 'Algorithmic Proof',
                'description' => 'Prove through algorithmic construction or computation',
                'keywords' => ['algorithm', 'compute', 'calculate', 'procedure', 'step'],
                'structure' => 'Step-by-step procedure that demonstrates truth'
            ]
        ];
    }

    /**
     * Initialize math domains
     */
    private function initializeMathDomains() {
        $this->mathDomains = [
            'real_analysis' => [
                'name' => 'Real Analysis',
                'keywords' => ['limit', 'continuous', 'derivative', 'integral', 'epsilon', 'delta', 'sequence', 'series'],
                'concepts' => ['Limits', 'Continuity', 'Differentiability', 'Integrability', 'Convergence', 'Topology'],
                'notation' => ['ε', 'δ', '∀', '∃', 'lim', '∫', '∑', 'sup', 'inf']
            ],
            'calculus' => [
                'name' => 'Calculus',
                'keywords' => ['derivative', 'integral', 'limit', 'continuity', 'critical point', 'extrema'],
                'concepts' => ['Derivatives', 'Integrals', 'Limits', 'Series', 'Taylor Expansion', 'Optimization'],
                'notation' => ['f\'(x)', 'dy/dx', '∫', '∑', 'lim']
            ],
            'discrete_math' => [
                'name' => 'Discrete Mathematics',
                'keywords' => ['combination', 'permutation', 'graph', 'tree', 'recurrence', 'modulo', 'divisible'],
                'concepts' => ['Combinatorics', 'Graph Theory', 'Recurrence Relations', 'Number Theory', 'Set Theory'],
                'notation' => ['C(n,k)', 'P(n,k)', '|V|', 'gcd', '≡ (mod)']
            ],
            'algebra' => [
                'name' => 'Algebra',
                'keywords' => ['equation', 'system', 'polynomial', 'factor', 'root', 'matrix', 'determinant'],
                'concepts' => ['Linear Equations', 'Polynomials', 'Systems', 'Matrices', 'Group Theory', 'Ring Theory'],
                'notation' => ['x²', 'A⁻¹', 'det(A)', '∈ ℝ', 'Span']
            ],
            'geometry' => [
                'name' => 'Geometry',
                'keywords' => ['angle', 'triangle', 'parallel', 'perpendicular', 'congruent', 'similar', 'tangent'],
                'concepts' => ['Euclidean Geometry', 'Coordinate Geometry', 'Transformations', 'Congruence', 'Similarity'],
                'notation' => ['∠', '≅', '∼', '⊥', '∥']
            ],
            'logic' => [
                'name' => 'Logic & Set Theory',
                'keywords' => ['proposition', 'logical', 'set', 'element', 'subset', 'union', 'intersection', 'quantifier'],
                'concepts' => ['Propositional Logic', 'Predicate Logic', 'Set Operations', 'Relations', 'Functions'],
                'notation' => ['∧', '∨', '¬', '∀', '∃', '∈', '⊂', '∪', '∩']
            ],
            'linear_algebra' => [
                'name' => 'Linear Algebra',
                'keywords' => ['vector', 'matrix', 'eigenvalue', 'eigenvector', 'basis', 'dimension', 'rank', 'null space'],
                'concepts' => ['Vectors', 'Matrices', 'Eigenvalues', 'Linear Transformations', 'Spaces', 'Norms'],
                'notation' => ['⃗v', 'A⁻¹', 'det(A)', 'rank(A)', 'λ']
            ],
            'probability' => [
                'name' => 'Probability & Statistics',
                'keywords' => ['probability', 'distribution', 'mean', 'variance', 'expectation', 'random', 'independence'],
                'concepts' => ['Probability Distributions', 'Expected Value', 'Variance', 'Covariance', 'Statistical Tests'],
                'notation' => ['P(A)', 'E[X]', 'Var(X)', 'σ', '∼']
            ]
        ];
    }

    /**
     * Detect the mathematical domain from proof content
     * @param string $statement - Theorem statement or proof text
     * @return string - Domain key, defaults to 'real_analysis'
     */
    public function detectMathDomain($statement) {
        $statement_lower = strtolower($statement);
        $maxScore = 0;
        $detectedDomain = 'real_analysis';

        foreach ($this->mathDomains as $domain => $info) {
            $keywordMatches = 0;
            foreach ($info['keywords'] as $keyword) {
                if (stripos($statement_lower, $keyword) !== false) {
                    $keywordMatches++;
                }
            }

            if ($keywordMatches > $maxScore) {
                $maxScore = $keywordMatches;
                $detectedDomain = $domain;
            }
        }

        return $detectedDomain;
    }

    /**
     * Detect the proof type being used
     * @param string $proof - The proof text
     * @return string - Proof type key
     */
    public function detectProofType($proof) {
        $proof_lower = strtolower($proof);
        $maxScore = 0;
        $detectedType = 'direct';

        foreach ($this->proofTypes as $type => $info) {
            $keywordMatches = 0;
            foreach ($info['keywords'] as $keyword) {
                $count = substr_count($proof_lower, $keyword);
                $keywordMatches += $count;
            }

            if ($keywordMatches > $maxScore) {
                $maxScore = $keywordMatches;
                $detectedType = $type;
            }
        }

        return $detectedType;
    }

    /**
     * Get domain-specific guidance for a proof
     * @param string $domain - Math domain
     * @param string $statement - Theorem statement
     * @param string $proofType - Type of proof being used
     * @return array - Guidance and tips
     */
    public function getDomainGuidance($domain, $statement, $proofType = null) {
        $domain = $this->sanitizeDomain($domain);
        if (!isset($this->mathDomains[$domain])) {
            $domain = 'real_analysis';
        }

        $domainInfo = $this->mathDomains[$domain];
        
        $guidance = [
            'domain' => $domainInfo['name'],
            'concepts' => $domainInfo['concepts'],
            'notation' => $domainInfo['notation'],
            'tips' => $this->getDomaianSpecificTips($domain, $proofType)
        ];

        return $guidance;
    }

    /**
     * Get domain-specific tips
     */
    private function getDomaianSpecificTips($domain, $proofType = null) {
        $tips = [];

        switch ($domain) {
            case 'real_analysis':
                $tips = [
                    'Always use ε-δ notation for limits',
                    'Show all inequality manipulations step-by-step',
                    'Apply Triangle Inequality explicitly when needed',
                    'For limits, clearly separate: ∀ε>0, ∃δ>0',
                    'Define all variables and their domains'
                ];
                break;

            case 'calculus':
                $tips = [
                    'Show the definition of derivative or integral',
                    'Include intermediate steps in calculations',
                    'For series, indicate convergence tests used',
                    'For optimization, identify critical points',
                    'Check endpoints and boundary conditions'
                ];
                break;

            case 'discrete_math':
                $tips = [
                    'Clearly state counting principles used',
                    'For graphs, specify vertex and edge sets',
                    'Show base cases for inductive proofs on integers',
                    'Use proper binomial notation: C(n,k) or (n choose k)',
                    'For recurrences, solve the characteristic equation'
                ];
                break;

            case 'algebra':
                $tips = [
                    'Each equation manipulation must be justified',
                    'For systems, use substitution or elimination clearly',
                    'Factor polynomials completely over the correct field',
                    'For matrices, show all intermediate multiplication steps',
                    'State any invertibility assumptions'
                ];
                break;

            case 'geometry':
                $tips = [
                    'Define all angles and segments clearly',
                    'Use angle measures and congruence statements',
                    'Apply geometry postulates and theorems by name',
                    'For coordinate geometry, show distance/slope calculations',
                    'Use proper geometric notation (∠, ≅, ∼, ⊥, ∥)'
                ];
                break;

            case 'logic':
                $tips = [
                    'Use proper truth tables for logical statements',
                    'Distinguish between ∀ (for all) and ∃ (exists)',
                    'For set proofs, show element-by-element inclusion',
                    'Use proper set notation: ∈, ⊂, ∪, ∩, ∖',
                    'For relations, verify reflexivity, symmetry, transitivity'
                ];
                break;

            case 'linear_algebra':
                $tips = [
                    'For eigenvectors, solve (A - λI)v = 0',
                    'Use proper matrix notation and dimensions',
                    'Show singular value decomposition when relevant',
                    'For bases, verify linear independence',
                    'Compute rank and nullity correctly'
                ];
                break;

            case 'probability':
                $tips = [
                    'For distributions, use proper PDF/PMF notation',
                    'Show expectation calculations explicitly',
                    'Verify independence when assumed',
                    'Use proper probability notation: P(A), P(A|B)',
                    'For Bayes theorem, identify prior and likelihood'
                ];
                break;
        }

        return $tips;
    }

    /**
     * Validate proof structure based on proof type
     * @param string $proofType - Type of proof
     * @param string $proof - The proof text
     * @return array - Validation results
     */
    public function validateProofStructure($proofType, $proof) {
        $proofType = $this->sanitizeProofType($proofType);
        $issues = [];
        $proof_lower = strtolower($proof);

        switch ($proofType) {
            case 'induction':
                if (stripos($proof, 'base case') === false) {
                    $issues[] = 'Missing base case for induction';
                }
                if (stripos($proof, 'inductive step') === false && stripos($proof, 'assume') === false) {
                    $issues[] = 'Missing inductive step or inductive hypothesis';
                }
                break;

            case 'contradiction':
                if (stripos($proof, 'assume') === false && stripos($proof, 'suppose') === false) {
                    $issues[] = 'Missing assumption of negation';
                }
                if (stripos($proof, 'contradicts') === false && stripos($proof, 'contradiction') === false) {
                    $issues[] = 'Missing explicit contradiction statement';
                }
                break;

            case 'cases':
                if (substr_count($proof_lower, 'case') < 2) {
                    $issues[] = 'Proof by cases should have multiple cases';
                }
                break;
        }

        return [
            'proofType' => $proofType,
            'isValid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Get proof type recommendations
     */
    public function getProofTypeRecommendations($statement) {
        $recommendations = [];
        
        $statement_lower = strtolower($statement);

        // Check for induction indicators
        if (preg_match('/\bfor all\b|\ball.*\bn\b|\bintegers\b|\binduction\b/i', $statement)) {
            $recommendations[] = [
                'type' => 'induction',
                'reason' => 'Statement appears to be about all integers or natural numbers'
            ];
        }

        // Check for existence claims
        if (preg_match('/\bthere exists\b|\bexist\b|\bfind\b|\bconstruct\b/i', $statement)) {
            $recommendations[] = [
                'type' => 'construction',
                'reason' => 'Statement claims existence - consider constructive proof'
            ];
        }

        // Check for contradictions
        if (preg_match('/\bnot\b|\bcanno\b|\bimpossib\b|\bcontradict\b/i', $statement)) {
            $recommendations[] = [
                'type' => 'contradiction',
                'reason' => 'Negation in statement suggests proof by contradiction'
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'direct',
                'reason' => 'Direct proof is a good starting point'
            ];
        }

        return $recommendations;
    }

    /**
     * Sanitize and validate domain input
     */
    private function sanitizeDomain($domain) {
        $domain = strtolower(trim($domain));
        $domain = str_replace(' ', '_', $domain);
        return $domain;
    }

    /**
     * Sanitize and validate proof type input
     */
    private function sanitizeProofType($type) {
        $type = strtolower(trim($type));
        $type = str_replace(' ', '_', $type);
        return $type;
    }

    /**
     * Get all available domains
     */
    public function getAllDomains() {
        return array_map(function($domain, $info) {
            return [
                'key' => $domain,
                'name' => $info['name'],
                'concepts' => $info['concepts']
            ];
        }, array_keys($this->mathDomains), $this->mathDomains);
    }

    /**
     * Get all available proof types
     */
    public function getAllProofTypes() {
        return array_map(function($type, $info) {
            return [
                'key' => $type,
                'name' => $info['name'],
                'description' => $info['description']
            ];
        }, array_keys($this->proofTypes), $this->proofTypes);
    }
}
