# PostgreSQL Setup - Quick Start Guide

## 🚀 TLDR - Get PostgreSQL Running in 5 Minutes

### 1. Install PostgreSQL (5 min)
**Download:** https://www.postgresql.org/download/windows/

During installation:
- **Port:** 5432 (default)
- **Username:** postgres
- **Password:** (choose one)

**Verify:**
```powershell
psql --version
```

### 2. Create Database and User (2 min)

Open **PowerShell as Administrator:**

```powershell
# Create database
createdb -U postgres lean4_ai_db

# Create user with password
psql -U postgres -c "CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';"

# Grant privileges
psql -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;"

# Verify
psql -U lean4_user -d lean4_ai_db -c "SELECT NOW();"
```

### 3. Import Schema (1 min)

From project root:
```powershell
# Windows
cd database
psql -U lean4_user -d lean4_ai_db -f postgres_schema.sql

# Verify
psql -U lean4_user -d lean4_ai_db -c "SELECT table_name FROM information_schema.tables WHERE table_schema='public';"
```

### 4. Verify Setup (Automated)

```powershell
cd database
php setup-postgres.php
```

**Expected output:**
```
✓ PostgreSQL server is running
✓ Database 'lean4_ai_db' exists
✓ Schema exists with 10+ tables
✓ Essential tables structure OK
✓ Theorem data populated
✓ ALL CHECKS PASSED
```

### 5. Start Testing

```powershell
# From project root
php -S localhost:8080

# Open browser
http://localhost:8080/frontend/pages/theorems.html
```

---

## 📋 Configuration Checklist

- [x] PostgreSQL 16 installed
- [ ] Database `lean4_ai_db` created
- [ ] User `lean4_user` created
- [ ] Schema imported from `database/postgres_schema.sql`
- [ ] Backend config updated in `backend/config/database.php`
- [ ] Setup verification passed: `php database/setup-postgres.php`
- [ ] PHP server started: `php -S localhost:8080`
- [ ] Frontend loads: `http://localhost:8080/frontend/pages/theorems.html`
- [ ] Can select and submit theorems

---

## 🔧 Troubleshooting

### "psql: command not found"
PostgreSQL installer didn't add to PATH.

**Fix:**
```powershell
# Add PostgreSQL bin to PATH
$env:PATH += ";C:\Program Files\PostgreSQL\16\bin"

# Or add permanently to Windows environment variables
```

### "FATAL: remaining connection slots reserved"
PostgreSQL server crashed. Restart it:

```powershell
net stop postgresql-x64-16
net start postgresql-x64-16
```

### "role 'lean4_user' does not exist"
User wasn't created. Run:

```powershell
psql -U postgres
CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';
\q
```

### "database 'lean4_ai_db' does not exist"
Database wasn't created. Run:

```powershell
createdb -U postgres lean4_ai_db
```

### "WRONGPASSWORD"
Password mismatch between:
1. PostgreSQL user password
2. `backend/config/database.php` password

**Fix:** Update both to match, then test:
```powershell
psql -U lean4_user -d lean4_ai_db -c "SELECT NOW();"
```

### Connection works in psql but not PHP
PHP might be missing PostgreSQL extension.

**Check:**
```powershell
php -m | findstr pgsql
```

**If missing:** Install PostgreSQL extension for your PHP version

---

## 🔐 Credentials Reference

| Item | Value |
|------|-------|
| Host | localhost |
| Port | 5432 |
| Database | lean4_ai_db |
| User | lean4_user |
| Password | lean4_password_123 |
| Admin User | postgres |
| Config File | backend/config/database.php |

---

## 📊 Database Contents

After setup, your database will have:

**Tables (10+):**
- `users` - User accounts
- `theorems` - 50+ Real Analysis theorems
- `theorem_categories` - Theorem organization
- `proof_attempts` - User proof submissions
- `proof_conversations` - AI tutoring history
- `user_preferences` - User settings
- `submissions` - Legacy proof submissions
- And more...

**Startup Data:**
- 50+ theorems with Lean code
- 5 theorem categories
- Sample user data (optional)

---

## ☁️ Cloud Deployment (Render.com)

Once local PostgreSQL works, deploying to cloud takes 5 minutes:

1. Create Render account: https://render.com
2. Create PostgreSQL database
3. Update `DATABASE_URL` environment variable
4. System auto-detects and connects

See: [RENDER_POSTGRES_DEPLOYMENT.md](../../RENDER_POSTGRES_DEPLOYMENT.md)

---

## 🎯 Next Steps After Setup

1. ✅ PostgreSQL running locally
2. ✅ Schema imported
3. ✅ Backend configured
4. ⏭️  Test API endpoints:
   ```powershell
   curl http://localhost:8080/backend/api/theorems.php?action=get_theorems
   ```
5. ⏭️  Create user account in web interface
6. ⏭️  Submit a proof and test AI tutoring
7. ⏭️  Explore [AI_TUTOR_README.md](../../AI_TUTOR_README.md) for full features

---

## 📞 Support

**Having issues?**
1. Check troubleshooting section above
2. Run verification: `php database/setup-postgres.php`
3. Check PostgreSQL logs: `C:\Program Files\PostgreSQL\16\data\pg_log\`
4. Review: [POSTGRESQL_MIGRATION_GUIDE.md](POSTGRESQL_MIGRATION_GUIDE.md)

**Did it work?**  
Great! Now see [HOW_TO_RUN.md](../../HOW_TO_RUN.md) for system overview and [AI_TUTOR_README.md](../../AI_TUTOR_README.md) for API documentation.

