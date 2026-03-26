# Render Deployment - Login/Signup Fix Guide

## The Problem

When deployed to Render, login and signup fail with:
```
Database is currently unavailable. Please try again later.
```

## Root Cause

The application is trying to connect to a MySQL database at `localhost:3306`, which doesn't exist in Render. It falls into "demo mode" and **blocks all signup requests**.

**Why this happens:**
1. Render doesn't have local MySQL
2. The `DATABASE_URL` environment variable may not be properly set or parsed
3. The fallback connection to `localhost` fails
4. Auth API blocks signup when database is unavailable

## Solution

### Step 1: Test Connection Locally

First, verify the database connection works locally:

```bash
# Test from c:\Project\v11
php -r "
require 'backend/config/database.php';
\$db = new Database();
if (\$db->isDemoMode()) {
    echo 'ERROR: Demo mode active - database connection failed\n';
} else {
    echo 'SUCCESS: Database connected\n';
}
"
```

**Expected output**: `SUCCESS: Database connected`

If you see demo mode, MySQL isn't running. Run:
```bash
# Windows - start MySQL
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe"
```

### Step 2: Verify Render Configuration

Check that `render.yaml` is correctly configured:

```yaml
# Should have this:
envVars:
  - key: DATABASE_URL
    fromDatabase:
      name: axio-db
      property: connectionString

databases:
  - name: axio-db
    databaseName: axio_production
    user: axio_user
```

### Step 3: Fix DATABASE_URL Parsing (DONE ✅)

The database connection code has been updated to:
- Use PHP's `parse_url()` instead of regex
- Better handle PostgreSQL and MySQL URLs
- Proper error logging

**Changes made:**
- ✅ [backend/config/database.php](backend/config/database.php#L31-L59) - Improved URL parsing

### Step 4: Deploy to Render

```bash
# Push to git (Render auto-deploys)
git add .
git commit -m "Fix: Improve DATABASE_URL parsing for Render compatibility"
git push origin main
```

Render will:
1. Create PostgreSQL database (axio_production)
2. Set DATABASE_URL environment variable
3. Deploy the updated code
4. Initialize schema (if auto-setup enabled)

### Step 5: Initialize Database Schema in Render

If the tables don't exist after deployment, run:

```bash
# Via Render Dashboard:
# 1. Go to your service
# 2. Click "Shell"
# 3. Run database setup
mysql --host=$DATABASE_HOST --user=$DATABASE_USER --password=$DATABASE_PASS $DATABASE_NAME < database/schema.sql
```

Or use the auto-setup endpoint:

```bash
# Trigger auto-setup
curl https://your-render-app.onrender.com/backend/config/auto-setup.php
```

## Debugging Steps

### Check Render Logs

**In Render Dashboard:**
1. Select your service
2. Go to "Logs"
3. Look for database connection messages

**Expect to see:**
```
Database: Connecting postgres://username:***@hostname:5432/axio_production
Database: Connection SUCCESS
```

**If you see:**
```
Database: Connection FAILED - could not find driver
```

→ PostgreSQL PDO driver not installed (need `php-pgsql`)

### Test Login Endpoint Directly

```bash
curl -X POST https://your-app.onrender.com/backend/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"login","username":"testuser","password":"testpass123"}'
```

**Expected response:**
```json
{
  "success": true,
  "message": "Login successful.",
  "user_id": 1,
  "username": "testuser"
}
```

If response shows `demo_mode: true`, database is still not connected.

### Test Signup Endpoint

```bash
curl -X POST https://your-app.onrender.com/backend/api/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"register","username":"newuser","password":"password123"}'
```

**If blocked:**
```json
{
  "success": false,
  "message": "Database is currently unavailable.",
  "demo_mode": true
}
```

→ Database connection issue

**If successful:**
```json
{
  "success": true,
  "message": "User registered successfully.",
  "user_id": 2,
  "username": "newuser"
}
```

## Common Issues & Fixes

### Issue 1: "DATABASE_URL not set"

**Symptoms**: Logs show connection attempt to localhost

**Fix**:
1. Go to Render Dashboard
2. Select service → Environment
3. Verify `DATABASE_URL` is listed
4. If missing, create database first:
   - Dashboard → New → PostgreSQL
   - Link to your service
5. Redeploy

### Issue 2: "could not find driver"

**Symptoms**: Error mentions PDO driver

**Fix**: Add to `build command` in render.yaml:
```yaml
buildCommand: |
  apt-get update && apt-get install -y php-pgsql
  composer install --no-dev
```

### Issue 3: "Connection refused"

**Symptoms**: Can't connect even with correct credentials

**Fix**:
1. Verify database is running (in Render)
2. Check hostname includes `.onrender.com`
3. Verify IP whitelist allows all IPs (Render requirement)

### Issue 4: "SQLSTATE[3D000]: Invalid catalog name"

**Symptoms**: Database name doesn't exist

**Fix**: Create the database:
```bash
# In Render shell:
createdb -U $DATABASE_USER axio_production

# Then initialize schema:
psql -U $DATABASE_USER axio_production < database/schema.sql
```

## Verification Checklist

After deploying, verify:

- [ ] Render shows database is attached
- [ ] `DATABASE_URL` env var is set
- [ ] Logs show "Connection SUCCESS"
- [ ] Can login with test account
- [ ] Can create new account (signup works)
- [ ] Settings persist after login
- [ ] Can access theorems page

## Rollback (If Needed)

If deployment breaks things:

```bash
# Revert to last working commit
git revert HEAD
git push origin main
```

Render will auto-redeploy with previous version.

## Files Changed

**Fixed in this session:**
- `backend/config/database.php` - Better URL parsing
- `backend/api/auth.php` - Clearer error messages

**No changes needed to:**
- `render.yaml` - Already correct
- `frontend/pages/auth.html` - Works as-is
- `backend/models/User.php` - Works as-is

## Next Steps

1. **Deploy** the updated code
2. **Check logs** in Render dashboard
3. **Test** login/signup endpoints
4. **Initialize** database schema if needed
5. **Verify** full functionality

## Support

If signup/login still fails after these steps:

1. Check Render logs for specific error
2. Look at the Database section in Render dashboard
3. Verify PostgreSQL service is running
4. Try manual schema initialization

**Logs location**: Render Dashboard → Your Service → Logs tab

---

**Status**: Application now properly parses DATABASE_URL and handles PostgreSQL connections from Render. Deploy the changes and verify in Render logs.
