# Axio Signup Database Fix - TODO Checklist

**Status: 4/8 COMPLETE** | Local 100% ✅ | auth.php demo rejection FIXED | Render DATABASE_URL next

## Plan Overview
Fix \"Database connection unavailable\" error during signup:
- Root cause: Demo mode masks real DB connection failures on Render.
- Solution: Expose real errors, fix local diagnostics, improve connection handling.

## Step-by-Step Tasks

### [✅] 1. Fix Local Diagnostics Path Error\n- **File:** `backend/config/auto-setup.php` ✅\n- **Result:** Fixed path → Full test PASSED (user ID=13 created)"
</xai:function_call}





<xai:function_call name="edit_file">
<parameter name="path">c:/Project/v11/TODO.md

### [ ] 2. Run & Analyze Local DB Diagnostics
- **Commands:**
  ```
  php backend/check-database.php
  php backend/test-signup-complete.php  
  php backend/signup-diagnostic.php
  ```
- **Expected:** Identify local MySQL/SQLite issues. Note user/pass/host.
- **If demoMode:** Create SQLite DB + tables.

### [ ] 3. Enhance Database.php Connection Diagnostics
- **File:** `backend/config/database.php`
- **Add:** `public function isConnected() { return $this->pdo !== null && !$this->demoMode; }`
- **Improve:** Log exact PDOException messages, SSL settings for PG.
- **Test:** Local connection success.

### [ ] 4. Disable Demo Mode Fake-Success in Auth
- **File:** `backend/api/auth.php` 
- **Change:** In `register()` if(demoMode) → return `{'success': false, 'message': 'Database connection failed: ' . error_details}`
- **Keep:** Demo login fallback only.
- **Test:** Signup returns real DB error JSON.

### [ ] 5. Fix & Test Local SQLite Fallback
- **Create:** `data/axio.db` dir + schema.
- **Run:** `php database/init-local-sqlite.php` (create if needed).
- **Verify:** Signup creates real user in SQLite.

### [ ] 6. Frontend Error Handling Enhancement
- **File:** `frontend/pages/auth.html`
- **Add:** If `data.demo_mode` → \"Database temporarily down. Dev login: testuser/testpass123\"
- **Test:** Better UX on failure.

### [ ] 7. Production Render Fixes
- **DATABASE_URL:** Validate PG format, test connection.
- **runtime.txt:** Ensure `php-8.2 pgsql pdo_pgsql`.
- **Deploy:** Test full signup → DB insert.

### [ ] 8. Final Validation & Completion
- **Tests:**
  - Local: Full signup → user in DB.
  - Render: Signup → real user created.
  - Edge: Username exists, password short.
- **Cleanup:** Update TODO.md → `attempt_completion`.

## Commands to Run After Each Step
```
# Local test after each code change
php backend/check-database.php
php backend/test-signup-complete.php
```

## Current Status
- Plan approved ✅
- Ready for Step 1 execution.

**Next: Fix auto-setup.php path → Test diagnostics locally.**

