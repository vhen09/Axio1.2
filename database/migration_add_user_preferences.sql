-- MIGRATION: Add user_preferences table for tutorial state persistence
-- Run this on existing databases to support the new tutorial flow system

-- Create the user_preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
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

-- Populate preferences for existing users (if any)
INSERT IGNORE INTO user_preferences (user_id) 
SELECT id FROM users WHERE id NOT IN (SELECT user_id FROM user_preferences);

-- Verify migration
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_preferences FROM user_preferences;
