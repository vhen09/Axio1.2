/**
 * AXIO PostgreSQL Schema
 * Converted from MySQL with optimizations for PostgreSQL
 * 
 * Key Differences:
 * - INT AUTO_INCREMENT → BIGSERIAL
 * - ENUM('value1', 'value2') → Standard ENUM type
 * - TIMESTAMP → TIMESTAMP WITH TIME ZONE
 * - FULLTEXT → tsvector with GIN indexes
 * - JSONB for efficient JSON storage and querying
 */

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Enable full-text search
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- ============================================================================
-- USER & AUTHENTICATION TABLES
-- ============================================================================

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created ON users(created_at DESC);

-- ============================================================================
-- USER PREFERENCES & SETTINGS
-- ============================================================================

CREATE TABLE user_preferences (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    theme VARCHAR(20) DEFAULT 'light',
    font_size VARCHAR(20) DEFAULT 'medium',
    language VARCHAR(10) DEFAULT 'en',
    latex_tutorial_completed BOOLEAN DEFAULT FALSE,
    preferred_domains JSONB DEFAULT '[]'::jsonb,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_prefs_user ON user_preferences(user_id);
CREATE INDEX idx_user_prefs_tutorial ON user_preferences(latex_tutorial_completed);

-- ============================================================================
-- THEOREM LIBRARY
-- ============================================================================

CREATE TYPE difficulty_level AS ENUM ('beginner', 'intermediate', 'advanced', 'expert');

CREATE TABLE theorem_categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INTEGER DEFAULT 0,
    icon_unicode VARCHAR(10),
    color_hex VARCHAR(7) DEFAULT '#1e3a8a',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_categories_name ON theorem_categories(name);
CREATE INDEX idx_categories_display ON theorem_categories(display_order);

-- Main theorem table with optimized columns for AXIO
CREATE TABLE theorems (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES theorem_categories(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    statement TEXT NOT NULL,
    lean_code TEXT NOT NULL,
    natural_language_description TEXT,
    difficulty_level difficulty_level DEFAULT 'intermediate',
    tags JSONB DEFAULT '[]'::jsonb,
    prerequisites JSONB DEFAULT '[]'::jsonb,
    related_theorems JSONB DEFAULT '[]'::jsonb,
    proof_hints TEXT,
    examples TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INTEGER DEFAULT 0,
    success_rate NUMERIC(5,2) DEFAULT 0,
    avg_attempts INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    
    -- Full-text search vector
    search_vector tsvector GENERATED ALWAYS AS (
        to_tsvector('english', name || ' ' || COALESCE(natural_language_description, ''))
    ) STORED
);

-- Strategic indexes for theorems
CREATE INDEX idx_theorems_category ON theorems(category_id);
CREATE INDEX idx_theorems_difficulty ON theorems(difficulty_level);
CREATE INDEX idx_theorems_active ON theorems(is_active) WHERE is_active = TRUE;
CREATE INDEX idx_theorems_usage ON theorems(usage_count DESC);
CREATE INDEX idx_theorems_success ON theorems(success_rate DESC);
CREATE INDEX idx_theorems_search ON theorems USING GIN(search_vector);
CREATE INDEX idx_theorems_tags ON theorems USING GIN(tags);
CREATE INDEX idx_theorems_prerequisites ON theorems USING GIN(prerequisites);

-- ============================================================================
-- PROOF ATTEMPTS & VERIFICATION
-- ============================================================================

CREATE TYPE verification_status AS ENUM ('pending', 'success', 'failed', 'error');

CREATE TABLE proof_attempts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    theorem_id BIGINT NOT NULL REFERENCES theorems(id) ON DELETE CASCADE,
    natural_language_input TEXT NOT NULL,
    generated_lean_code TEXT,
    verification_status verification_status DEFAULT 'pending',
    verification_output TEXT,
    ai_feedback TEXT,
    score INTEGER,
    time_spent_seconds INTEGER,
    language_detected VARCHAR(20) DEFAULT 'en',
    is_successful BOOLEAN GENERATED ALWAYS AS (verification_status = 'success') STORED,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Critical indexes for proof_attempts (hot table)
CREATE INDEX idx_proof_attempts_user ON proof_attempts(user_id);
CREATE INDEX idx_proof_attempts_theorem ON proof_attempts(theorem_id);
CREATE INDEX idx_proof_attempts_user_theorem ON proof_attempts(user_id, theorem_id);
CREATE INDEX idx_proof_attempts_status ON proof_attempts(verification_status);
CREATE INDEX idx_proof_attempts_created ON proof_attempts(created_at DESC);
CREATE INDEX idx_proof_attempts_user_recent ON proof_attempts(user_id, created_at DESC);
CREATE INDEX idx_proof_attempts_successful ON proof_attempts(user_id, verified_at DESC) WHERE is_successful = TRUE;

-- ============================================================================
-- AI CONVERSATION HISTORY
-- ============================================================================

CREATE TABLE proof_conversations (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    proof_attempt_id BIGINT REFERENCES proof_attempts(id) ON DELETE CASCADE,
    theorem_id BIGINT NOT NULL REFERENCES theorems(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL,  -- 'user', 'assistant', 'system'
    message TEXT NOT NULL,
    tokens_used INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_conversations_user ON proof_conversations(user_id);
CREATE INDEX idx_conversations_attempt ON proof_conversations(proof_attempt_id);
CREATE INDEX idx_conversations_created ON proof_conversations(created_at DESC);
CREATE INDEX idx_conversations_user_recent ON proof_conversations(user_id, created_at DESC);

-- ============================================================================
-- SCORING & ANALYTICS
-- ============================================================================

CREATE TABLE scores (
    id BIGSERIAL PRIMARY KEY,
    proof_attempt_id BIGINT NOT NULL REFERENCES proof_attempts(id) ON DELETE CASCADE,
    logical_correctness INTEGER DEFAULT 0,
    completeness INTEGER DEFAULT 0,
    clarity INTEGER DEFAULT 0,
    rigor INTEGER DEFAULT 0,
    efficiency INTEGER DEFAULT 0,
    total_score INTEGER GENERATED ALWAYS AS (
        logical_correctness + completeness + clarity + rigor + efficiency
    ) STORED,
    grade VARCHAR(2) GENERATED ALWAYS AS (
        CASE 
            WHEN total_score >= 90 THEN 'A'
            WHEN total_score >= 80 THEN 'B'
            WHEN total_score >= 70 THEN 'C'
            WHEN total_score >= 60 THEN 'D'
            ELSE 'F'
        END
    ) STORED,
    feedback TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_scores_attempt ON scores(proof_attempt_id);
CREATE INDEX idx_scores_grade ON scores(grade);
CREATE INDEX idx_scores_created ON scores(created_at DESC);

-- ============================================================================
-- USER STATISTICS (DENORMALIZED FOR PERFORMANCE)
-- ============================================================================

CREATE TABLE user_stats (
    user_id BIGINT PRIMARY KEY REFERENCES users(id) ON DELETE CASCADE,
    total_attempts INTEGER DEFAULT 0,
    total_successes INTEGER DEFAULT 0,
    success_rate NUMERIC(5,2) DEFAULT 0,
    avg_score NUMERIC(5,2) DEFAULT 0,
    max_score INTEGER DEFAULT 0,
    total_theorems_solved INTEGER DEFAULT 0,
    favorite_domain VARCHAR(50),
    last_activity TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_user_stats_success ON user_stats(success_rate DESC);
CREATE INDEX idx_user_stats_last_activity ON user_stats(last_activity DESC);

-- ============================================================================
-- ACTIVITY LOGS (For audit trail & analytics)
-- ============================================================================

CREATE TABLE activity_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(50) NOT NULL,  -- 'login', 'proof_submitted', 'theorem_viewed', etc.
    resource_type VARCHAR(50),    -- 'theorem', 'proof_attempt', 'user'
    resource_id BIGINT,
    metadata JSONB DEFAULT '{}'::jsonb,
    ip_address INET,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_user ON activity_logs(user_id) WHERE user_id IS NOT NULL;
CREATE INDEX idx_activity_action ON activity_logs(action);
CREATE INDEX idx_activity_created ON activity_logs(created_at DESC);
CREATE INDEX idx_activity_user_action ON activity_logs(user_id, action, created_at DESC);

-- ============================================================================
-- SUBMISSION TRACKING (For backward compatibility)
-- ============================================================================

CREATE TABLE submissions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    input_text TEXT NOT NULL,
    proof_attempt_id BIGINT REFERENCES proof_attempts(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_submissions_user ON submissions(user_id);
CREATE INDEX idx_submissions_created ON submissions(created_at DESC);

-- ============================================================================
-- TRIGGERS & FUNCTIONS
-- ============================================================================

-- Function to update user_stats on proof attempt insertion
CREATE OR REPLACE FUNCTION update_user_stats_on_proof_insert()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO user_stats (user_id, total_attempts)
    VALUES (NEW.user_id, 1)
    ON CONFLICT (user_id) DO UPDATE SET
        total_attempts = user_stats.total_attempts + 1,
        updated_at = CURRENT_TIMESTAMP;
    
    -- If successful, increment successes
    IF NEW.verification_status = 'success' THEN
        UPDATE user_stats
        SET total_successes = total_successes + 1
        WHERE user_id = NEW.user_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER proof_attempt_stats_insert AFTER INSERT ON proof_attempts
FOR EACH ROW EXECUTE FUNCTION update_user_stats_on_proof_insert();

-- Function to update theorem usage and success rate
CREATE OR REPLACE FUNCTION update_theorem_stats()
RETURNS TRIGGER AS $$
DECLARE
    total_count INTEGER;
    success_count INTEGER;
BEGIN
    UPDATE theorems
    SET usage_count = usage_count + 1
    WHERE id = NEW.theorem_id;
    
    -- Update success rate
    SELECT COUNT(*), SUM(CASE WHEN verification_status = 'success' THEN 1 ELSE 0 END)
    INTO total_count, success_count
    FROM proof_attempts
    WHERE theorem_id = NEW.theorem_id;
    
    UPDATE theorems
    SET success_rate = CASE 
        WHEN total_count > 0 THEN (success_count::NUMERIC / total_count) * 100
        ELSE 0
    END
    WHERE id = NEW.theorem_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER theorem_stats_update AFTER INSERT ON proof_attempts
FOR EACH ROW EXECUTE FUNCTION update_theorem_stats();

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Apply to all tables with updated_at column
CREATE TRIGGER users_updated_at BEFORE UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER user_preferences_updated_at BEFORE UPDATE ON user_preferences
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER theorems_updated_at BEFORE UPDATE ON theorems
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER proof_attempts_updated_at BEFORE UPDATE ON proof_attempts
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER scores_updated_at BEFORE UPDATE ON scores
FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- INITIAL DATA (Seed)
-- ============================================================================

-- Insert theorem categories
INSERT INTO theorem_categories (name, description, display_order, icon_unicode) VALUES
('Sequences and Series', 'Properties of infinite sequences and their sums', 1, 'Σ'),
('Continuity', 'Limits and continuous functions', 2, '∞'),
('Derivatives and Integrals', 'Differentiation and integration theory', 3, '∫'),
('Metric Spaces', 'Abstract distance and topology', 4, '◯'),
('Convergence', 'Rates and modes of convergence', 5, '→'),
('Compactness', 'Compact sets and their properties', 6, '◾');

-- ============================================================================
-- MATERIALIZED VIEWS (For analytics & reporting)
-- ============================================================================

CREATE MATERIALIZED VIEW popular_theorems AS
SELECT 
    t.id,
    t.name,
    t.difficulty_level,
    c.name as category_name,
    COUNT(pa.id) as attempt_count,
    SUM(CASE WHEN pa.verification_status = 'success' THEN 1 ELSE 0 END) as success_count,
    ROUND(
        (SUM(CASE WHEN pa.verification_status = 'success' THEN 1 ELSE 0 END)::NUMERIC / COUNT(pa.id)) * 100, 2
    ) as success_rate,
    ROUND(AVG(CASE WHEN s.total_score IS NOT NULL THEN s.total_score ELSE 0 END), 2) as avg_score
FROM theorems t
LEFT JOIN theorem_categories c ON t.category_id = c.id
LEFT JOIN proof_attempts pa ON t.id = pa.theorem_id
LEFT JOIN scores s ON pa.id = s.proof_attempt_id
WHERE t.is_active = TRUE
GROUP BY t.id, t.name, t.difficulty_level, c.name
ORDER BY attempt_count DESC;

CREATE INDEX idx_popular_theorems_attempts ON popular_theorems(attempt_count DESC);

CREATE MATERIALIZED VIEW user_progress_summary AS
SELECT 
    u.id as user_id,
    u.username,
    COUNT(DISTINCT pa.id) as total_attempts,
    COUNT(DISTINCT CASE WHEN pa.verification_status = 'success' THEN pa.id END) as successful_attempts,
    COUNT(DISTINCT pa.theorem_id) as unique_theorems_attempted,
    ROUND(
        (COUNT(DISTINCT CASE WHEN pa.verification_status = 'success' THEN pa.id END)::NUMERIC / 
         NULLIF(COUNT(DISTINCT pa.id), 0) * 100), 2
    ) as success_rate,
    ROUND(AVG(CASE WHEN s.total_score IS NOT NULL THEN s.total_score ELSE 0 END), 2) as avg_score,
    MAX(pa.created_at) as last_activity
FROM users u
LEFT JOIN proof_attempts pa ON u.id = pa.user_id
LEFT JOIN scores s ON pa.id = s.proof_attempt_id
GROUP BY u.id, u.username;

CREATE INDEX idx_user_progress_success ON user_progress_summary(success_rate DESC);

-- ============================================================================
-- COMMENTS (For documentation)
-- ============================================================================

COMMENT ON TABLE theorems IS 'Main theorem library for the AXIO system. Stores Real Analysis theorems with Lean code and metadata.';
COMMENT ON TABLE proof_attempts IS 'Records of students attempting proofs. Critical for tracking learning progress.';
COMMENT ON COLUMN proof_attempts.verification_status IS 'Status of Lean code verification: pending (waiting for verification), success (valid proof), failed (invalid proof), error (verification system error)';
COMMENT ON TABLE user_stats IS 'Denormalized statistics table for fast user dashboard loads. Updated via triggers.';
COMMENT ON TABLE activity_logs IS 'Audit trail for all user actions. Used for analytics and debugging.';
