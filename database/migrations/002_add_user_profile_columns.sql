-- Fix: Add missing columns to users table for signup

-- Check if columns exist and add them if they don't
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) DEFAULT '',
ADD COLUMN IF NOT EXISTS email VARCHAR(100) DEFAULT '';

-- Verify the structure
DESCRIBE users;