-- REANA System Setup and Testing Guide
-- Run these commands to verify everything is working

-- ============================================================================
-- STEP 1: DATABASE SETUP
-- ============================================================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS lean4_ai_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lean4_ai_app;

-- Run schema (creates all tables)
SOURCE database/schema.sql;

-- Run seed data (loads all theorems)
SOURCE database/seed_theorems.sql;

-- Verify data loaded correctly
SELECT 
    c.name AS category,
    COUNT(t.id) AS theorem_count,
    GROUP_CONCAT(t.difficulty_level) AS difficulties
FROM theorem_categories c
LEFT JOIN theorems t ON c.id = t.category_id
GROUP BY c.id, c.name
ORDER BY c.display_order;

-- Expected output: 9 categories with ~50 total theorems


-- ============================================================================
-- STEP 2: TEST THEOREM API
-- ============================================================================

-- Test in browser or use curl:

-- Get all categories
-- http://localhost/backend/api/theorems.php?action=get_categories

-- Get all theorems
-- http://localhost/backend/api/theorems.php?action=get_theorems

-- Search theorems
-- http://localhost/backend/api/theorems.php?action=search_theorems&q=convergence

-- Get specific theorem
-- http://localhost/backend/api/theorems.php?action=get_theorem_by_id&theorem_id=1


-- ============================================================================
-- STEP 3: TEST NATURAL LANGUAGE CONVERSION
-- ============================================================================

-- Test with curl or Postman:
/*
POST http://localhost/backend/api/proof.php?action=convert_to_lean
Content-Type: application/json

{
  "theorem_id": 1,
  "natural_language_proof": "Gamit ang Archimedean property, para sa kahit anong real number x, may natural number n na mas malaki sa x. Ito ay dahil ang set ng natural numbers ay unbounded sa taas."
}
*/


-- ============================================================================
-- STEP 4: TEST COMPLETE PROOF FLOW
-- ============================================================================

/*
POST http://localhost/backend/api/proof.php?action=complete_proof_flow
Content-Type: application/json

{
  "theorem_id": 2,
  "natural_language_proof": "Assume L1 ≠ L2. Kunin ang ε = |L1 - L2| / 2. By definition ng convergence, may N1 at N2. Para sa n ≥ max(N1, N2), makakakuha tayo ng contradiction using triangle inequality.",
  "user_id": 1
}
*/

-- This should return:
-- 1. Lean code generated
-- 2. Verification result
-- 3. Saved proof attempt ID


-- ============================================================================
-- STEP 5: CHECK SAVED PROOFS
-- ============================================================================

-- View all proof attempts
SELECT 
    pa.id,
    u.username,
    t.name AS theorem,
    pa.verification_status,
    pa.score,
    pa.created_at
FROM proof_attempts pa
JOIN users u ON pa.user_id = u.id
JOIN theorems t ON pa.theorem_id = t.id
ORDER BY pa.created_at DESC;


-- ============================================================================
-- STEP 6: VERIFY FRONTEND
-- ============================================================================

-- Open these pages in browser:

-- 1. Theorem Browser
-- http://localhost/frontend/pages/theorems.html

-- 2. Proof Assistant
-- http://localhost/frontend/pages/tutor.html?theorem_id=1

-- 3. Dashboard
-- http://localhost/frontend/pages/dashboard.html


-- ============================================================================
-- USEFUL QUERIES FOR TESTING
-- ============================================================================

-- Get most popular theorems
SELECT name, usage_count 
FROM theorems 
ORDER BY usage_count DESC 
LIMIT 10;

-- Get theorems by difficulty
SELECT difficulty_level, COUNT(*) as count
FROM theorems
WHERE is_active = TRUE
GROUP BY difficulty_level;

-- Get recent proof attempts with details
SELECT 
    t.name AS theorem,
    SUBSTRING(pa.natural_language_input, 1, 100) AS proof_excerpt,
    pa.verification_status,
    pa.score,
    pa.created_at
FROM proof_attempts pa
JOIN theorems t ON pa.theorem_id = t.id
ORDER BY pa.created_at DESC
LIMIT 20;

-- Search theorems (full text search)
SELECT 
    name, 
    statement,
    difficulty_level
FROM theorems
WHERE MATCH(name, natural_language_description) AGAINST('limit convergence' IN BOOLEAN MODE)
   OR name LIKE '%limit%';


-- ============================================================================
-- PERFORMANCE INDEXES (if needed)
-- ============================================================================

-- Add indexes for better performance
CREATE INDEX idx_theorem_name ON theorems(name);
CREATE INDEX idx_proof_attempt_date ON proof_attempts(created_at);
CREATE INDEX idx_user_theorem ON proof_attempts(user_id, theorem_id);


-- ============================================================================
-- SAMPLE TEST DATA
-- ============================================================================

-- Insert a test user (if not exists)
INSERT IGNORE INTO users (username, password) 
VALUES ('test_user', '$2y$10$test_password_hash');

-- Test proof attempt
INSERT INTO proof_attempts (
    user_id, 
    theorem_id, 
    natural_language_input,
    generated_lean_code,
    verification_status,
    score
) VALUES (
    1,
    1,
    'Para sa kahit anong real number x, dahil unbounded ang natural numbers, may n > x.',
    'theorem archimedean_property (x : ℝ) : ∃ n : ℕ, (n : ℝ) > x := by sorry',
    'success',
    100
);


-- ============================================================================
-- VERIFICATION CHECKLIST
-- ============================================================================

-- Run these checks:

-- ✓ All 9 categories loaded
SELECT COUNT(*) FROM theorem_categories; -- Should be 9

-- ✓ All theorems loaded
SELECT COUNT(*) FROM theorems WHERE is_active = TRUE; -- Should be 40+

-- ✓ Each category has theorems
SELECT category_id, COUNT(*) 
FROM theorems 
GROUP BY category_id; -- All should have entries

-- ✓ All difficulty levels represented
SELECT difficulty_level, COUNT(*) 
FROM theorems 
GROUP BY difficulty_level; -- Should have all 4 levels

-- ✓ JSON fields are valid
SELECT id, name, JSON_VALID(tags) as tags_valid
FROM theorems
WHERE NOT JSON_VALID(tags); -- Should return empty

-- ✓ Prerequisites are valid JSON
SELECT id, name, JSON_VALID(prerequisites) as prereq_valid
FROM theorems
WHERE NOT JSON_VALID(prerequisites); -- Should return empty


-- ============================================================================
-- CLEANUP (if needed to reset)
-- ============================================================================

-- WARNING: This deletes all data!
/*
TRUNCATE TABLE proof_conversations;
TRUNCATE TABLE proof_attempts;
TRUNCATE TABLE theorems;
TRUNCATE TABLE theorem_categories;

-- Then re-run seed_theorems.sql
*/
