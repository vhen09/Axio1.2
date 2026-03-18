# Live Deployment Fix - Render.com

## Problem
Frontend JS gets HTML error page instead of JSON → 'Unexpected token <'

## Solution
1. **Render Dashboard** → Shell tab
2. ```
rm backend/api/auth.php
cp backend/api/auth-fixed.php backend/api/auth.php
```
3. **Manual Deploy**

## Verify
curl -X POST your-render-url/backend/api/auth.php -H "Content-Type: application/json" -d '{"action":"register","username":"test","password":"test"}'
Expected JSON:
```json
{"success":false,"message":"Database unavailable..."}
```

## Root Cause Fixed
auth.php syntax → Now returns proper JSON error when DB unavailable

**Deploy now → Signup shows clear DB error (not HTML crash)**

