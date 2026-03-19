CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    input_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    score INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id)
);

-- REANA Theorem Library
CREATE TABLE theorem_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE theorems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    statement TEXT NOT NULL,
    lean_code TEXT NOT NULL,
    natural_language_description TEXT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    tags JSON,
    prerequisites JSON,
    related_theorems JSON,
    proof_hints TEXT,
    examples TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES theorem_categories(id),
    INDEX idx_category (category_id),
    INDEX idx_difficulty (difficulty_level),
    INDEX idx_active (is_active),
    FULLTEXT KEY ft_name_description (name, natural_language_description)
);

-- User's Proof Attempts
CREATE TABLE proof_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theorem_id INT NOT NULL,
    natural_language_input TEXT NOT NULL,
    generated_lean_code TEXT,
    verification_status ENUM('pending', 'success', 'failed', 'error') DEFAULT 'pending',
    verification_output TEXT,
    ai_feedback TEXT,
    score INT,
    time_spent_seconds INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (theorem_id) REFERENCES theorems(id) ON DELETE CASCADE,
    INDEX idx_user_theorem (user_id, theorem_id),
    INDEX idx_status (verification_status)
);

-- AI Conversation History for Context
CREATE TABLE proof_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proof_attempt_id INT NOT NULL,
    message_type ENUM('user', 'assistant', 'system') NOT NULL,
    message_content TEXT NOT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proof_attempt_id) REFERENCES proof_attempts(id)
);

-- User Preferences and Onboarding State
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    latex_skill_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    tutorial_completed BOOLEAN DEFAULT FALSE,
    tutorial_skipped BOOLEAN DEFAULT FALSE,
    first_login_completed BOOLEAN DEFAULT FALSE,
    onboarding_step INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_tutorial_completed (tutorial_completed)
);