-- User Settings Table
-- Comprehensive centralized storage for all user settings
-- Replaces scattered localStorage with persistent database storage

CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    
    -- UI Preferences
    theme VARCHAR(20) DEFAULT 'light' COMMENT 'Theme: light, dark, auto',
    fontSize VARCHAR(20) DEFAULT 'normal' COMMENT 'Font size: small, normal, large',
    learningLanguage VARCHAR(10) DEFAULT 'en' COMMENT 'ISO language code',
    
    -- Proof Settings
    proofMode VARCHAR(50) DEFAULT 'step-by-step' COMMENT 'Default proof mode',
    difficultyLevel VARCHAR(20) DEFAULT 'beginner' COMMENT 'Difficulty level',
    preferredDomains JSON DEFAULT '["algebra", "calculus"]' COMMENT 'Preferred math domains',
    
    -- Feature Flags
    autoSave VARCHAR(10) DEFAULT 'true' COMMENT 'Auto-save drafts',
    showPreview VARCHAR(10) DEFAULT 'true' COMMENT 'Show proof preview',
    showHints VARCHAR(10) DEFAULT 'true' COMMENT 'Show step hints',
    autoTutorial VARCHAR(10) DEFAULT 'false' COMMENT 'Auto-start tutorial',
    
    -- Notification Settings
    scoreNotifications VARCHAR(10) DEFAULT 'true' COMMENT 'Notify on score',
    feedbackNotifications VARCHAR(10) DEFAULT 'true' COMMENT 'Notify on feedback',
    soundNotifications VARCHAR(10) DEFAULT 'false' COMMENT 'Enable sound',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
