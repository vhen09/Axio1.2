-- Migration script to add first_name, last_name, and email columns to users table
-- This script is safe to run multiple times (uses IF NOT EXISTS or ALTER IGNORE)

-- Add columns if they don't already exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT '';

-- Verify the columns were added
SELECT 'User table migration complete. Columns added:' as status;
DESCRIBE users;
