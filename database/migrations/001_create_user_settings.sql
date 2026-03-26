-- User Settings Table
-- Comprehensive centralized storage for all user settings
-- Replaces scattered localStorage with persistent database storage

CREATE TABLE IF NOT EXISTS user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    
    -- ===== APPEARANCE SETTINGS =====
    theme VARCHAR(20) DEFAULT 'light' COMMENT 'Theme: light, dark, auto',
    font_size VARCHAR(20) DEFAULT 'medium' COMMENT 'Font size: small, medium, large',
    color_scheme VARCHAR(50) DEFAULT 'default' COMMENT 'Color scheme preset',
    primary_color VARCHAR(7) DEFAULT '#059669' COMMENT 'Primary color hex',
    accent_color VARCHAR(7) DEFAULT '#0ea5e9' COMMENT 'Accent color hex',
    layout_mode VARCHAR(20) DEFAULT 'comfortable' COMMENT 'Layout: compact, comfortable',
    
    -- ===== NOTIFICATION SETTINGS =====
    email_notifications BOOLEAN DEFAULT true COMMENT 'Send email notifications',
    in_app_notifications BOOLEAN DEFAULT true COMMENT 'In-app notifications',
    maintenance_alerts BOOLEAN DEFAULT true COMMENT 'Maintenance alerts',
    reminder_notifications BOOLEAN DEFAULT true COMMENT 'Reminder notifications',
    sound_notifications BOOLEAN DEFAULT false COMMENT 'Sound notifications',
    
    -- ===== PROOF SETTINGS =====
    enable_validation BOOLEAN DEFAULT true COMMENT 'Enable data validation',
    require_confirmation_delete BOOLEAN DEFAULT true COMMENT 'Require delete confirmation',
    enable_activity_logging BOOLEAN DEFAULT true COMMENT 'Log all activities',
    enable_audit_trail BOOLEAN DEFAULT true COMMENT 'Maintain audit trail',
    require_admin_approval BOOLEAN DEFAULT false COMMENT 'Require admin approval',
    
    -- ===== LEARNING SETTINGS =====
    show_tooltips BOOLEAN DEFAULT true COMMENT 'Show tooltips',
    enable_tutorials BOOLEAN DEFAULT true COMMENT 'Enable tutorials',
    show_help_guides BOOLEAN DEFAULT true COMMENT 'Show help guides',
    enable_walkthrough_mode BOOLEAN DEFAULT false COMMENT 'Walkthrough mode',
    enable_auto_suggestions BOOLEAN DEFAULT true COMMENT 'Auto suggestions',
    
    -- ===== MATHEMATICAL DOMAIN SETTINGS =====
    enable_math_calculations BOOLEAN DEFAULT true COMMENT 'Math calculations',
    enable_formula_validation BOOLEAN DEFAULT true COMMENT 'Formula validation',
    enable_real_time_computation BOOLEAN DEFAULT true COMMENT 'Real-time computation',
    enable_graph_rendering BOOLEAN DEFAULT true COMMENT 'Graph rendering',
    math_precision VARCHAR(20) DEFAULT '4_decimal' COMMENT 'Precision: 2,4,6 decimal',
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
