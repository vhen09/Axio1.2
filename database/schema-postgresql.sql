-- PostgreSQL Schema for AXIO Lean4 AI Web App
-- Convert from MySQL to PostgreSQL syntax

-- Drop existing tables if any
DROP TABLE IF EXISTS scores CASCADE;
DROP TABLE IF EXISTS submissions CASCADE;
DROP TABLE IF EXISTS theorem_categories CASCADE;
DROP TABLE IF EXISTS theorems CASCADE;
DROP TABLE IF EXISTS user_preferences CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User Preferences table
CREATE TABLE user_preferences (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL,
    latex_skill_level VARCHAR(50) DEFAULT 'beginner',
    tutorial_completed BOOLEAN DEFAULT FALSE,
    tutorial_skipped BOOLEAN DEFAULT FALSE,
    first_login_completed BOOLEAN DEFAULT FALSE,
    onboarding_step INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Theorem Categories table
CREATE TABLE theorem_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Theorems table
CREATE TABLE theorems (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INTEGER REFERENCES theorem_categories(id) ON DELETE SET NULL,
    description TEXT,
    lean_code TEXT,
    natural_language_description TEXT,
    difficulty_level VARCHAR(50) CHECK (difficulty_level IN ('beginner', 'intermediate', 'advanced', 'expert')),
    tags JSON,
    prerequisites JSON,
    related_theorems JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Submissions table (for proof attempts)
CREATE TABLE submissions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    theorem_id INTEGER REFERENCES theorems(id) ON DELETE SET NULL,
    input_text JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scores table
CREATE TABLE scores (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    submission_id INTEGER REFERENCES submissions(id) ON DELETE CASCADE,
    score INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_user_preferences_user_id ON user_preferences(user_id);
CREATE INDEX idx_theorems_category ON theorems(category_id);
CREATE INDEX idx_submissions_user ON submissions(user_id);
CREATE INDEX idx_submissions_theorem ON submissions(theorem_id);
CREATE INDEX idx_scores_user ON scores(user_id);
CREATE INDEX idx_scores_submission ON scores(submission_id);

-- Seed basic theorem categories
INSERT INTO theorem_categories (name, description) VALUES
('Sequences', 'Theorems about convergence and limits of sequences'),
('Series', 'Theorems about infinite series and convergence tests'),
('Continuity', 'Theorems about continuous functions'),
('Derivatives', 'Theorems about differentiation and derivatives'),
('Integration', 'Theorems about integration and Riemann integrals'),
('Limits', 'Theorems about limits of functions'),
('Real Numbers', 'Foundational theorems about real number properties');
