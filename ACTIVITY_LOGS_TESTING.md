# Activity Logs System - Testing & Troubleshooting Guide

## Quick Start: Testing Activity Logs

1. **Access the Debug Console**: `http://localhost:8080/frontend/pages/session-debug.html`
2. **Click "Test Login"** - This logs you in and shows the response
3. **Check Browser Cookies** - Verify PHPSESSID cookie exists
4. **Click "Test Activity Logs API"** - This retrieves your activities

If step 4 returns **401 Unauthorized**, the session isn't persisting. Follow the debugging steps below.

---

## Understanding the Activity Tracking System

### Components

**Backend**:
- `backend/models/ActivityLog.php` - Database operations for tracking
- `backend/api/auth.php` - Login/signup with automatic activity logging
- `backend/api/activity-logs.php` - REST API for retrieving activities
- `backend/config/database.php` - Database connection and schema

**Frontend**:
- `frontend/pages/activity-logs.html` - Activity dashboard UI
- `frontend/pages/session-debug.html` - Debug console (this guide)
- `frontend/js/auth-handler.js` - Login/signup handler

**Database**:
- Table: `activity_logs`
- Columns: user_id, action, resource_type, resource_id, metadata, ip_address, created_at

### Expected Flow

```
1. User logs in (auth.php?action=login)
   ↓
2. Server validates credentials
   ↓
3. Server sets $_SESSION['user_id'] and $_SESSION['username']
   ↓
4. Browser receives PHPSESSID cookie
   ↓
5. User navigates to activity-logs.html
   ↓
6. User clicks "My Activities" button
   ↓
7. Frontend sends fetch request to activity-logs.php with credentials: 'include'
   ↓
8. Browser includes PHPSESSID cookie in headers
   ↓
9. activity-logs.php calls session_start()
   ↓
10. Session is reconstructed from session file
    ↓
11. $_SESSION['user_id'] is available
    ↓
12. API returns user's activities
```

---

## Troubleshooting: If You Get 401 Unauthorized

### Step 1: Check Session Cookie Exists

Use Developer Tools (F12 in Chrome):
1. Open browser DevTools → **Application** tab
2. Expand **Cookies** in left sidebar
3. Look for `localhost:8080` (or your domain)
4. You should see a cookie named **PHPSESSID**

**If PHPSESSID doesn't exist:**
- Try logging in again
- Check that login returns `success: true`
- Session might be failing to start

**If PHPSESSID exists:**
- Continue to Step 2

### Step 2: Use Session Debug Console

Navigate to: `http://localhost:8080/frontend/pages/session-debug.html`

1. **Click "Test Login"**
   - Should return `success: true`
   - Note the returned `user_id`

2. **Click "Check Browser Cookies"**
   - Should show PHPSESSID cookie
   - Copy the session ID value

3. **Click "Test Activity Logs API"**
   - If still 401, check the debug info in response
   - The debug object will show:
     ```json
     {
       "session_id": "abc123...",
       "session_user_id": null,
       "session_username": null
     }
     ```

### Step 3: Check PHP Error Logs

If available, check PHP error logs for insights:

**On Linux/Mac:**
```bash
tail -f /var/log/php-errors.log
# or check php.ini for error_log path
```

**On Windows (with PHP built-in server):**
- Windows doesn't have a standard log location
- Enable error logging by creating `backend/logs/php-errors.log`
- Add to `.htaccess` or PHP script:
  ```ini
  error_log /path/to/backend/logs/php-errors.log
  ```

### Step 4: Check Server Logs

The enhanced logging in `activity-logs.php` and `auth.php` writes to:

```php
error_log("message"); // Goes to PHP error log or configured log file
```

Look for lines like:
```
=== Activity Logs API Called ===
Session ID: abc123...
Session User ID: NOT SET
```

**If "Session User ID: NOT SET"**, the session file exists but doesn't contain user_id.

### Step 5: Session Configuration Check

Current session settings in `backend/api/auth.php` and `backend/api/activity-logs.php`:

```php
ini_set('session.cookie_samesite', 'Lax');      // Allow same-site navigation
ini_set('session.cookie_secure', false);          // Allow HTTP (localhost)
ini_set('session.cookie_httponly', true);         // Security: prevent JS access
ini_set('session.cookie_path', '/');              // Available to whole domain
```

**If the issue persists**, try less restrictive settings:

1. Edit `backend/api/activity-logs.php` and `backend/api/auth.php`
2. Change `session.cookie_samesite` from `'Lax'` to `'None'` (test only!)
3. Then change back to `'Lax'` after confirming it works

---

## Testing Without Browser

Use the provided test script:

```bash
php test-activity-logs.php
```

This will:
1. Connect to database
2. Check for existing users
3. List recent activities
4. Test manual logging

Expected output:
```
=== Activity Logs Test ===
✓ Database connected

--- Test 1: Retrieve all activities ---
Total activities found: 5
  - User 1: login (user) at 2024-01-15 10:30:45
  - User 1: signup (user) at 2024-01-15 10:25:30
  ...
```

---

## Common Issues & Solutions

### Issue 1: "Session User ID: NOT SET"

**Causes:**
- Session file not being persisted
- Session directory not writable
- PHP session handler configuration

**Solutions:**
1. Check PHP session.save_path:
   ```php
   echo ini_get('session.save_path');
   ```
   
2. Ensure directory is writable:
   ```bash
   chmod 777 /path/to/session/dir  # Linux
   ```

3. Try test login in demo mode:
   - Use `testuser` / `testpass123`
   - For demo mode with no database

### Issue 2: "PHPSESSID Cookie Not Showing"

**Causes:**
- SameSite=None requires HTTPS (not working on HTTP localhost)
- Cookie not being sent by server
- Browser blocking cookies

**Solutions:**
1. Change to `SameSite=Lax` (already done)

2. For HTTPS testing (advanced):
   ```bash
   openssl req -x509 -newkey rsa:2048 -keyout key.pem -out cert.pem -days 365
   # Then use PHP built-in server with HTTPS
   ```

3. Allow third-party cookies in browser settings (if testing cross-domain)

### Issue 3: "404 on activity-logs.php"

**Check:**
1. File exists at `backend/api/activity-logs.php`
2. API base URL is correct: `http://localhost:8080/backend/api`
3. No typos in fetch URL

### Issue 4: "Works in Demo Mode but Not with Database"

**Check:**
1. Can you log in successfully with real credentials?
2. Are activities being logged to database?
   ```bash
   php test-activity-logs.php
   ```
3. Is the activity_logs table created?
   ```sql
   SELECT * FROM activity_logs LIMIT 5;
   ```

---

## Advanced Testing

### Test with cURL (Command Line)

```bash
# First, login and capture PHPSESSID
RES=$(curl -s -c cookies.txt \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"testpass123"}' \
  http://localhost:8080/backend/api/auth.php?action=login)

echo "Login response:" $RES

# Then, use the same session/cookies to call activity logs API
curl -s -b cookies.txt \
  http://localhost:8080/backend/api/activity-logs.php?action=my_activities | jq .
```

Expected output:
```json
{
  "success": true,
  "user_id": 1,
  "activities": [...],
  "count": 5
}
```

### Test with PHP Session File

Check if session file exists:

```bash
# Find session save path
php -r "echo ini_get('session.save_path');"

# List session files
ls /tmp/php*  # Linux
dir %temp%    # Windows
```

Session file name format: `sess_<SESSION_ID>`

Check contents:
```bash
cat /tmp/sess_abc123def456
# Output should show: user_id|i:1;username|s:8:"testuser";
```

---

## Fixing the Session Issue (In Progress)

### Changes Made (Commit: 9f6f658b)

1. **Session Configuration Update**
   - Changed `SameSite=None` to `SameSite=Lax` for HTTP compatibility
   - Applied to both `auth.php` and `activity-logs.php`
   
2. **Enhanced Logging**
   - Both APIs now log session details
   - Debug info includes cookies, session ID, and session keys

3. **Debug Console**
   - New `session-debug.html` page with testing tools
   - Test login, check cookies, trace session persistence

### Next Steps If Still Not Working

1. **Run session debug console**
   - Go to `http://localhost:8080/frontend/pages/session-debug.html`
   - Run each test and note the results

2. **Check PHP error logs** for any session-related errors

3. **Verify database connectivity**
   - Run `php test-activity-logs.php`
   - Confirm activities are being stored

4. **Consider token-based auth** (alternative approach)
   - If session persistence doesn't work
   - Use JWT tokens instead
   - Would require frontend changes

---

## Success Verification

When working correctly:

1. **Login** → User ID saved in session
2. **Navigate to Activity Logs** → Dashboard loads
3. **Click "My Activities"** → Table shows login activity
4. **Click "Login History"** → Shows all login/logout records
5. **Click "Statistics"** → Shows access stats

---

## Questions?

If the activity logs aren't working:

1. Use the session debug console: `session-debug.html`
2. Check the browser DevTools Network tab
3. Review the response JSON for error details
4. Check server error logs
5. Run `php test-activity-logs.php` to verify database side
