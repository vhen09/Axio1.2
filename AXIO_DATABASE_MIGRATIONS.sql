-- Database Migration Guide for AXIO General Mathematics Support
-- This file documents recommended schema extensions for tracking domain and proof-type data
-- These migrations are OPTIONAL and can be implemented in Phase 2

-- ==============================================================================
-- MIGRATION 1: Extend proof_attempts table to track mathematical domain
-- ==============================================================================
-- Adds domain and proof_type fields for better analytics and personalization

ALTER TABLE proof_attempts 
ADD COLUMN math_domain VARCHAR(50) DEFAULT 'real_analysis' AFTER theorem_id,
ADD COLUMN proof_type VARCHAR(50) DEFAULT 'direct' AFTER math_domain,
ADD COLUMN domain_detected_at TIMESTAMP NULL DEFAULT NULL AFTER proof_type,
ADD INDEX idx_domain (math_domain),
ADD INDEX idx_proof_type (proof_type);

-- Example: Track that a student solved a calculus problem using direct proof
-- INSERT: math_domain='calculus', proof_type='direct'


-- ==============================================================================
-- MIGRATION 2: Create domain_performance table
-- ==============================================================================
-- Track student progress across different mathematical domains

CREATE TABLE IF NOT EXISTS domain_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    math_domain VARCHAR(50) NOT NULL,
    total_attempts INT DEFAULT 0,
    successful_attempts INT DEFAULT 0,
    avg_time_seconds INT DEFAULT 0,
    last_attempt_at TIMESTAMP NULL DEFAULT NULL,
    mastery_level ENUM('novice', 'beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'novice',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_domain (user_id, math_domain),
    INDEX idx_mastery (mastery_level)
);

-- Example query: Find domains where student struggles
-- SELECT math_domain, successful_attempts/total_attempts as success_rate
-- FROM domain_performance 
-- WHERE user_id = 1 AND mastery_level IN ('novice', 'beginner')
-- ORDER BY success_rate ASC;


-- ==============================================================================
-- MIGRATION 3: Create proof_type_performance table
-- ==============================================================================
-- Track student proficiency with different proof methods

CREATE TABLE IF NOT EXISTS proof_type_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    proof_type VARCHAR(50) NOT NULL,
    total_attempts INT DEFAULT 0,
    successful_attempts INT DEFAULT 0,
    avg_time_seconds INT DEFAULT 0,
    last_attempt_at TIMESTAMP NULL DEFAULT NULL,
    proficiency_score FLOAT DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_proof_type (user_id, proof_type),
    INDEX idx_proficiency (proficiency_score)
);

-- Example: Track which proof methods student finds easiest/hardest
-- SELECT proof_type, proficiency_score FROM proof_type_performance
-- WHERE user_id = 1 ORDER BY proficiency_score DESC;


-- ==============================================================================
-- MIGRATION 4: Create domain_concepts table
-- ==============================================================================
-- Map which concepts are covered in each domain for better feedback

CREATE TABLE IF NOT EXISTS domain_concepts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    math_domain VARCHAR(50) NOT NULL,
    concept_name VARCHAR(100) NOT NULL,
    description TEXT,
    essential BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_domain_concept (math_domain, concept_name),
    INDEX idx_domain (math_domain),
    INDEX idx_essential (essential)
);

-- Populate with Real Analysis concepts
INSERT INTO domain_concepts (math_domain, concept_name, description, essential) VALUES
('real_analysis', 'Limits', 'The fundamental concept of approaching a value', TRUE),
('real_analysis', 'Continuity', 'Functions that have no breaks or jumps', TRUE),
('real_analysis', 'Differentiability', 'Existence and properties of derivatives', TRUE),
('real_analysis', 'Integrability', 'Properties of definite and indefinite integrals', TRUE),
('real_analysis', 'Convergence', 'Behavior of sequences and series', TRUE),
('real_analysis', 'Topology', 'Open/closed sets, compactness, connectedness', FALSE),
('real_analysis', 'Completeness', 'The least upper bound property', FALSE);

-- Populate with Calculus concepts
INSERT INTO domain_concepts (math_domain, concept_name, description, essential) VALUES
('calculus', 'Derivative', 'Rate of change and instantaneous slope', TRUE),
('calculus', 'Integral', 'Accumulation and area under curves', TRUE),
('calculus', 'Limits', 'Foundation for derivative and integral definitions', TRUE),
('calculus', 'Series', 'Infinite sums and convergence tests', FALSE),
('calculus', 'Taylor Expansion', 'Polynomial approximations of functions', FALSE),
('calculus', 'Optimization', 'Finding maxima and minima', TRUE);

-- Populate with Discrete Math concepts
INSERT INTO domain_concepts (math_domain, concept_name, description, essential) VALUES
('discrete_math', 'Combinatorics', 'Counting arrangements and selections', TRUE),
('discrete_math', 'Graph Theory', 'Vertices, edges, paths, and connectivity', TRUE),
('discrete_math', 'Recurrence Relations', 'Recursive sequence definitions', TRUE),
('discrete_math', 'Number Theory', 'Properties of integers and divisibility', TRUE),
('discrete_math', 'Logic', 'Boolean algebra and logical operations', TRUE),
('discrete_math', 'Set Theory', 'Operations on sets and set properties', TRUE);


-- ==============================================================================
-- MIGRATION 5: Create student_concept_mastery table
-- ==============================================================================
-- Track individual student mastery of specific concepts within each domain

CREATE TABLE IF NOT EXISTS student_concept_mastery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    domain_concept_id INT NOT NULL,
    mastery_percentage FLOAT DEFAULT 0.0,
    times_practiced INT DEFAULT 0,
    last_practiced_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (domain_concept_id) REFERENCES domain_concepts(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_concept (user_id, domain_concept_id),
    INDEX idx_mastery (mastery_percentage)
);

-- Example: Find concepts student needs to practice
-- SELECT dc.concept_name, scm.mastery_percentage
-- FROM student_concept_mastery scm
-- JOIN domain_concepts dc ON scm.domain_concept_id = dc.id
-- WHERE scm.user_id = 1 AND dc.math_domain = 'calculus'
-- ORDER BY scm.mastery_percentage ASC;


-- ==============================================================================
-- MIGRATION 6: Create cross_domain_theorems table
-- ==============================================================================
-- Support theorems that can be proven in multiple domains

CREATE TABLE IF NOT EXISTS cross_domain_theorems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theorem_id INT NOT NULL,
    primary_domain VARCHAR(50) NOT NULL,
    secondary_domains JSON, -- ["algebra", "geometry", "real_analysis"]
    alternative_proofs JSON, -- Store different proof approaches for same theorem
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (theorem_id) REFERENCES theorems(id) ON DELETE CASCADE,
    UNIQUE KEY unique_theorem (theorem_id),
    INDEX idx_domains (primary_domain)
);

-- Example: The same algebraic identity can be proven multiple ways
-- INSERT INTO cross_domain_theorems (theorem_id, primary_domain, secondary_domains)
-- VALUES (42, 'algebra', '["geometry", "real_analysis"]');


-- ==============================================================================
-- MIGRATION 7: Create proof_annotations table
-- ==============================================================================
-- Store detailed annotations about proof steps for domain-specific feedback

CREATE TABLE IF NOT EXISTS proof_annotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proof_attempt_id INT NOT NULL,
    step_number INT NOT NULL,
    domain_concept_id INT,
    annotation_type ENUM('correct', 'incomplete', 'error', 'elegant', 'alternative') DEFAULT 'correct',
    feedback TEXT,
    suggestion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proof_attempt_id) REFERENCES proof_attempts(id) ON DELETE CASCADE,
    FOREIGN KEY (domain_concept_id) REFERENCES domain_concepts(id) ON DELETE SET NULL,
    INDEX idx_attempt (proof_attempt_id),
    INDEX idx_type (annotation_type)
);

-- Example: Annotate specific steps
-- INSERT INTO proof_annotations (proof_attempt_id, step_number, domain_concept_id, annotation_type, feedback)
-- VALUES (123, 1, 5, 'correct', 'Good use of limit definition');


-- ==============================================================================
-- MIGRATION 8: Add metadata column to proof_attempts
-- ==============================================================================
-- Store domain-specific metadata as JSON

ALTER TABLE proof_attempts 
ADD COLUMN domain_metadata JSON DEFAULT NULL AFTER ai_feedback;

-- Example structure:
-- {
--   "domain": "real_analysis",
--   "proof_type": "contradiction",
--   "epsilon_found": true,
--   "delta_notation_correct": true,
--   "quantifier_order_correct": true,
--   "concepts_used": ["Limits", "Continuity"],
--   "structural_score": 0.85,
--   "notation_score": 0.92
-- }


-- ==============================================================================
-- MIGRATION 9: Create domain_notation table
-- ==============================================================================
-- Map domain-specific notation for better symbol support

CREATE TABLE IF NOT EXISTS domain_notation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    math_domain VARCHAR(50) NOT NULL,
    notation_name VARCHAR(100) NOT NULL,
    latex_code VARCHAR(100) NOT NULL,
    unicode_display VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_notation (math_domain, notation_name),
    INDEX idx_domain (math_domain)
);

-- Example: Real Analysis notations
INSERT INTO domain_notation (math_domain, notation_name, latex_code, unicode_display) VALUES
('real_analysis', 'For All (quantifier)', '\\forall', '∀'),
('real_analysis', 'There Exists (quantifier)', '\\exists', '∃'),
('real_analysis', 'Epsilon (small positive)', '\\epsilon', 'ε'),
('real_analysis', 'Delta (small positive)', '\\delta', 'δ'),
('real_analysis', 'Limit', '\\lim', 'lim'),
('real_analysis', 'Integral', '\\int', '∫'),
('real_analysis', 'Sum', '\\sum', '∑'),
('real_analysis', 'Supremum', '\\sup', 'sup'),
('real_analysis', 'Infimum', '\\inf', 'inf');

-- Example: Discrete Math notations
INSERT INTO domain_notation (math_domain, notation_name, latex_code, unicode_display) VALUES
('discrete_math', 'Binomial Coefficient', '\\binom{n}{k}', '(ⁿk)'),
('discrete_math', 'Combination', 'C(n,k)', 'C(n,k)'),
('discrete_math', 'Permutation', 'P(n,k)', 'P(n,k)'),
('discrete_math', 'Ceiling', '\\lceil x \\rceil', '⌈x⌉'),
('discrete_math', 'Floor', '\\lfloor x \\rfloor', '⌊x⌋'),
('discrete_math', 'Modulo', 'a \\equiv b \\pmod{n}', 'a ≡ b (mod n)');


-- ==============================================================================
-- MIGRATION 10: Create query indexes for performance
-- ==============================================================================

-- Optimize domain/proof type queries
CREATE INDEX idx_proof_attempts_domain ON proof_attempts(math_domain, user_id);
CREATE INDEX idx_proof_attempts_type ON proof_attempts(proof_type, user_id);
CREATE INDEX idx_proof_attempts_status_domain ON proof_attempts(verification_status, math_domain);

-- Optimize concept mastery queries
CREATE INDEX idx_student_mastery_user_domain ON student_concept_mastery(user_id, mastery_percentage);

-- Optimize cross-domain queries
CREATE INDEX idx_theorems_domains ON theorems(category_id, difficulty_level);


-- ==============================================================================
-- VIEW: Student Learning Path
-- ==============================================================================
-- Combined view of student progress across domains and concepts

CREATE OR REPLACE VIEW student_learning_path AS
SELECT 
    u.id as user_id,
    u.username,
    dp.math_domain,
    dp.mastery_level,
    ROUND(dp.successful_attempts / NULLIF(dp.total_attempts, 0) * 100, 2) as success_rate,
    COUNT(DISTINCT pa.id) as proof_attempts_in_domain,
    AVG(DATEDIFF(NOW(), pa.created_at)) as avg_days_since_attempt
FROM users u
JOIN domain_performance dp ON u.id = dp.user_id
LEFT JOIN proof_attempts pa ON u.id = pa.user_id AND pa.math_domain = dp.math_domain
GROUP BY u.id, u.username, dp.math_domain, dp.mastery_level;

-- Query: Get student's full learning path
-- SELECT * FROM student_learning_path WHERE user_id = 1;


-- ==============================================================================
-- VIEW: Difficult Concepts by Domain
-- ==============================================================================
-- Identify concepts where students struggle

CREATE OR REPLACE VIEW struggling_concepts AS
SELECT 
    dc.math_domain,
    dc.concept_name,
    ROUND(AVG(scm.mastery_percentage), 2) as avg_mastery,
    COUNT(DISTINCT scm.user_id) as students_attempts,
    COUNT(CASE WHEN scm.mastery_percentage < 50 THEN 1 END) as struggling_students
FROM domain_concepts dc
LEFT JOIN student_concept_mastery scm ON dc.id = scm.domain_concept_id
WHERE dc.essential = TRUE
GROUP BY dc.id, dc.math_domain, dc.concept_name
HAVING avg_mastery < 70
ORDER BY avg_mastery ASC;

-- Query: Find easiest and hardest concepts
-- SELECT * FROM struggling_concepts ORDER BY avg_mastery ASC LIMIT 5;


-- ==============================================================================
-- Notes for Implementation
-- ==============================================================================

/*
PHASE 1 (CURRENT - No Database Changes Needed):
  ✅ GeneralMathProofService.php (stateless, no DB required)
  ✅ gm-proof.php API endpoint
  ✅ EquationComponent.js (unified equations)
  ✅ Clean LaTeX clipboard operations

PHASE 2 (Future - Optional Database Extensions):
  • Add math_domain, proof_type to proof_attempts (REQUIRED)
  • Create domain_performance tracking (RECOMMENDED)
  • Create proof_type_performance tracking (RECOMMENDED)
  • Add domain_metadata JSON field (OPTIONAL)

PHASE 3 (Advanced Analytics):
  • Implement concept mastery tracking
  • Create student learning paths
  • Enable difficulty adaptation by domain
  • Support multi-domain theorem proofs

DEPLOYMENT STRATEGY:
  1. Test Phase 1 features thoroughly
  2. Add Phase 2 migrations when ready
  3. Run migrations in test environment first
  4. Update API to populate new fields
  5. Backfill existing data (optional)
  6. Enable analytics dashboards
*/
