-- AXIO Hybrid Frontend - Database Migration
-- Adds support for frontend preference switching
--
-- Created: March 26, 2026
-- Purpose: Enable users to switch between Vanilla JS and React frontends
--
-- Usage:
-- PostgreSQL: psql -U lean4_user -d lean4_ai_db < migration_add_frontend_preference.sql
-- MySQL: mysql -u root -p lean4_ai_db < migration_add_frontend_preference.sql
-- SQLite: sqlite3 axio.db < migration_add_frontend_preference.sql

-- ===== PostgreSQL / Generic SQL =====

-- Add column if it doesn't exist (PostgreSQL)
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'user_preferences' 
    AND column_name = 'preferred_frontend'
  ) THEN
    ALTER TABLE user_preferences 
    ADD COLUMN preferred_frontend VARCHAR(20) DEFAULT 'vanilla';
    
    -- Create index for faster lookups
    CREATE INDEX idx_preferred_frontend ON user_preferences(preferred_frontend);
    
    RAISE NOTICE 'Column preferred_frontend added successfully';
  ELSE
    RAISE NOTICE 'Column preferred_frontend already exists';
  END IF;
END $$;

-- ===== Alternative for MySQL =====
-- Uncomment below if using MySQL instead:

-- ALTER TABLE user_preferences 
-- ADD COLUMN preferred_frontend VARCHAR(20) DEFAULT 'vanilla' AFTER user_id;

-- CREATE INDEX idx_preferred_frontend ON user_preferences(preferred_frontend);

-- ===== Verification Query =====
-- Run this to verify the column was added:
-- SELECT * FROM user_preferences LIMIT 1;
-- (Should show preferred_frontend column)

-- ===== Optional: Update Default Values =====
-- If you want to set preferences for existing users, uncomment:

-- UPDATE user_preferences 
-- SET preferred_frontend = 'vanilla' 
-- WHERE preferred_frontend IS NULL;

-- ===== Rollback (if needed) =====
-- To remove the column:
-- ALTER TABLE user_preferences 
-- DROP COLUMN preferred_frontend;

-- DROP INDEX idx_preferred_frontend;
