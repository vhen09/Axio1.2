# PostgreSQL Migration Guide - MySQL to PostgreSQL

## Overview
This guide migrates your Real Analysis Theorem Proving System from MySQL to PostgreSQL for both local development and cloud deployment.

**Timeline:** ~30 minutes  
**Difficulty:** Beginner-friendly with automated scripts

---

## Step 1: Install PostgreSQL on Windows

### Option A: Using PostgreSQL Installer (Recommended)

1. **Download** PostgreSQL 16 from https://www.postgresql.org/download/windows/
   - Choose "Windows x86-64" version

2. **Run the installer**
   - Install Location: `C:\Program Files\PostgreSQL\16`
   - Port: `5432` (default)
   - Username: `postgres`
   - Password: **Choose a strong password** (you'll need this)
   - Default database: `postgres`

3. **Verify Installation**
   ```powershell
   "C:\Program Files\PostgreSQL\16\bin\psql" --version
   ```
   Should show: `psql (PostgreSQL) 16.x`

### Option B: Using Chocolatey (If installed)
```bash
choco install postgresql
```

---

## Step 2: Create PostgreSQL Database

After PostgreSQL is installed, open **PowerShell as Administrator** and run:

```powershell
# Add PostgreSQL to PATH (if psql not recognized)
$env:PATH += ";C:\Program Files\PostgreSQL\16\bin"

# Connect to PostgreSQL
"C:\Program Files\PostgreSQL\16\bin\psql" -U postgres

# In psql prompt (psql=#), run:
CREATE DATABASE lean4_ai_db;
CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';
ALTER ROLE lean4_user SET client_encoding TO 'utf8';
ALTER ROLE lean4_user SET default_transaction_isolation TO 'read committed';
ALTER ROLE lean4_user SET default_transaction_deferrable TO on;
ALTER ROLE lean4_user SET default_time_zone TO 'UTC';
GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;
\q
```

**Or use createdb shortcut:**
```powershell
$env:PATH += ";C:\Program Files\PostgreSQL\16\bin"

createdb -U postgres lean4_ai_db
"C:\Program Files\PostgreSQL\16\bin\psql" -U postgres -c "CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';"
"C:\Program Files\PostgreSQL\16\bin\psql" -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;"
```

**Note:** Replace `lean4_password_123` with your actual password. Save this password!

---

## Step 3: Import PostgreSQL Schema

From your project root directory:

```powershell
cd C:\Project\v11\database
psql -U lean4_user -d lean4_ai_db -f postgres_schema.sql
```

When prompted for password, enter: `lean4_password_123`

**Or use full path if psql not in PATH:**
```powershell
"C:\Program Files\PostgreSQL\16\bin\psql" -U lean4_user -d lean4_ai_db -f postgres_schema.sql
```

**Expected Output:**
```
CREATE EXTENSION
CREATE EXTENSION
CREATE TYPE
CREATE TABLE
... (more CREATE TABLE messages)
```

---

## Step 4: Migrate Data from MySQL (If You Have Existing Data)

Run the automated migration script:

```powershell
cd C:\Project\v11
php database/migrate_mysql_to_postgres.php
```

This script will:
- Connect to both MySQL and PostgreSQL
- Copy all users and their submissions
- Handle data type conversions
- Create theorem library automatically

**What's Migrated:**
- Users and authentication data
- Submissions and scores
- Proof attempts and conversation history
- User preferences
- All 50+ theorems (auto-populated from seed data)

---

## Step 5: Update Backend Configuration

Edit [backend/config/database.php](../../backend/config/database.php) and change the default connection from MySQL to PostgreSQL:

**Change from:**
```php
private $host = 'localhost';
private $db = 'lean4_ai_app';
private $user = 'root';
private $pass = '';
```

**Change to:**
```php
private $dbType = 'postgresql'; // Add this
private $host = 'localhost';
private $port = '5432';
private $db = 'lean4_ai_db';
private $user = 'lean4_user';
private $pass = 'lean4_password_123';
```

---

## Step 6: Verify the Migration

### Test 1: Database Connection
```powershell
cd C:\Project\v11
php backend/check-database.php
```

Expected output:
```
✓ PostgreSQL Connection OK
✓ Database: lean4_ai_db
✓ User: lean4_user
✓ Tables: 10/10
```

### Test 2: Theorem Data
```powershell
cd C:\Project\v11
php backend/test-theorems.php
```

Expected output:
```
✓ Connected to PostgreSQL
✓ Theorem Categories: 5 found
✓ Theorems: 50+ loaded
✓ Sample theorem: "Archimedean Property"
```

### Test 3: Web Interface
1. Start the PHP server:
   ```powershell
   cd C:\Project\v11
   php -S localhost:8080
   ```

2. Open browser: http://localhost:8080/frontend/pages/theorems.html

3. Verify:
   - Theorem list loads
   - Can select a theorem
   - Can submit a proof

---

## Troubleshooting

### "Connection refused on 127.0.0.1:5432"
PostgreSQL is not running. Start it:
```powershell
net start postgresql-x64-16
```

### "Authentication failed for user lean4_user"
Password mismatch in config. Check:
- `backend/config/database.php` password matches PostgreSQL user password
- Database user exists: `"C:\Program Files\PostgreSQL\16\bin\psql" -U postgres -c "SELECT usename FROM pg_user;"`

### "Certificate verify failed"
If connecting to cloud PostgreSQL, disable SSL in config:
```php
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Remove or comment out:
    // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
```

### "Table does not exist"
Re-import schema:
```powershell
cd C:\Project\v11\database
"C:\Program Files\PostgreSQL\16\bin\psql" -U lean4_user -d lean4_ai_db -f postgres_schema.sql
```

---

## Environment Variables (For Cloud Deployment)

For Render.com or other cloud platforms, set:

```powershell
# In PowerShell or system environment variables:
$env:DATABASE_URL="postgresql://lean4_user:lean4_password_123@localhost:5432/lean4_ai_db"
```

The `database.php` file handles this automatically via `getenv('DATABASE_URL')`.

---

## Next Steps

1. ✅ **Local Development:** PostgreSQL running on localhost:5432
2. **Cloud Production:** Deploy to Render.com (see [RENDER_POSTGRES_DEPLOYMENT.md](../../RENDER_POSTGRES_DEPLOYMENT.md))
3. **Backup Strategy:** Run nightly backups of PostgreSQL

---

## Quick Reference

| Component | Value |
|-----------|-------|
| Database | lean4_ai_db |
| Host | localhost |
| Port | 5432 |
| User | lean4_user |
| Password | lean4_password_123 |
| Schema File | database/postgres_schema.sql |
| Migration Script | database/migrate_mysql_to_postgres.php |

