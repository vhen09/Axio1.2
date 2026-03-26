# 🚀 How to Run the REANA System

## Quick Start (3 Steps)

### **Windows:**
```bash
# 1. Open Command Prompt in lean4-ai-web-app folder
cd c:\Project\axio2.0\lean4-ai-web-app

# 2. Run the setup script
START_SYSTEM.bat

# 3. Open browser to:
http://localhost:8080/frontend/pages/theorems.html
```

### **Linux/Mac:**
```bash
# 1. Navigate to project folder
cd /path/to/lean4-ai-web-app

# 2. Make script executable and run
chmod +x start_system.sh
./start_system.sh

# 3. Open browser to:
http://localhost:8080/frontend/pages/theorems.html
```

---

## Prerequisites

Before running, make sure you have:

✅ **PHP 7.4+** installed
- Windows: Download from [windows.php.net](https://windows.php.net/download/)
- Mac: `brew install php`
- Linux: `sudo apt-get install php php-mysql php-curl`

✅ **MySQL/MariaDB** installed
- Windows: Install [XAMPP](https://www.apachefriends.org/)
- Mac: `brew install mysql`
- Linux: `sudo apt-get install mysql-server`

✅ **DeepSeek API Key** (optional, for AI features)
- Get from [platform.deepseek.com](https://platform.deepseek.com/)
- Add to `backend/config/deepseek.php`

---

## Manual Setup (Detailed)

### Step 1: Install Prerequisites

**Check if PHP is installed:**
```bash
php --version
```

**Check if MySQL is installed:**
```bash
mysql --version
```

### Step 2: Setup Database

**Option A: Using the script** (Recommended)
```bash
# Windows
START_SYSTEM.bat

# Linux/Mac
./start_system.sh
```

**Option B: Manual setup**
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE lean4_ai_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lean4_ai_app;

# Import schema
SOURCE database/schema.sql;

# Import theorems
SOURCE database/seed_theorems.sql;

# Verify
SELECT COUNT(*) FROM theorems;
# Should show 40+ theorems

EXIT;
```

### Step 3: Configure Application

**Edit database config:** `backend/config/database.php`
```php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "lean4_ai_app";
    private $username = "root";        // Change if needed
    private $password = "";            // Add your password
    // ...
}
```

**Edit DeepSeek config:** `backend/config/deepseek.php`
```php
<?php
return [
    'deepseek_api_url' => 'https://api.deepseek.com/v1/chat/completions',
    'deepseek_api_key' => 'YOUR_API_KEY_HERE',  // Add your key here
    'model' => 'deepseek-chat',
    'max_tokens' => 3000,
    'temperature' => 0.3
];
```

### Step 4: Start Server

**Using PHP built-in server:**
```bash
# Navigate to project root
cd c:\Project\axio2.0\lean4-ai-web-app

# Start server
php -S localhost:8080
```

**Using XAMPP:**
```bash
# Copy project to XAMPP htdocs
# C:\xampp\htdocs\lean4-ai-web-app

# Start XAMPP Control Panel
# Start Apache and MySQL

# Access at:
# http://localhost/lean4-ai-web-app/frontend/pages/theorems.html
```

### Step 5: Access the Application

Open your browser and go to:

🌐 **Main Pages:**
- **Theorem Browser:** `http://localhost:8080/frontend/pages/theorems.html`
- **Proof Assistant:** `http://localhost:8080/frontend/pages/tutor.html`
- **Dashboard:** `http://localhost:8080/frontend/pages/dashboard.html`
- **Submissions:** `http://localhost:8080/frontend/pages/submissions.html`
- **Scores:** `http://localhost:8080/frontend/pages/scores.html`

🔌 **API Endpoints:**
- **Theorems API:** `http://localhost:8080/backend/api/theorems.php?action=get_categories`
- **Proof API:** `http://localhost:8080/backend/api/proof.php`

---

## Testing the System

### Test Database Connection
```bash
# Open browser console and run:
fetch('http://localhost:8080/backend/api/theorems.php?action=get_categories')
  .then(r => r.json())
  .then(d => console.log(d));

# Should return: { success: true, categories: [...] }
```

### Test Theorem Loading
```bash
# Open Theorem Browser
http://localhost:8080/frontend/pages/theorems.html

# You should see:
# - 9 categories in sidebar
# - 40+ theorems in grid
# - Search functionality
# - Filter by difficulty
```

### Test Natural Language Conversion

**Using browser console:**
```javascript
fetch('http://localhost:8080/backend/api/proof.php?action=convert_to_lean', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    theorem_id: 1,
    natural_language_proof: 'Para sa kahit anong x, may n na mas malaki sa x'
  })
})
.then(r => r.json())
.then(d => console.log(d));
```

**Using curl:**
```bash
curl -X POST http://localhost:8080/backend/api/proof.php?action=convert_to_lean \
  -H "Content-Type: application/json" \
  -d '{"theorem_id":1,"natural_language_proof":"Para sa kahit anong x, may n"}'
```

---

## Troubleshooting

### ❌ "Database connection failed"
```bash
# Check MySQL is running
# Windows: Open Services, check MySQL is started
# Linux/Mac: sudo systemctl status mysql

# Verify credentials in backend/config/database.php
# Try: mysql -u root -p
```

### ❌ "Port 8080 already in use"
```bash
# Use different port
php -S localhost:8081

# Or find and kill process using port
# Windows: netstat -ano | findstr :8080
# Linux/Mac: lsof -ti:8080 | xargs kill
```

### ❌ "No theorems showing"
```bash
# Check database has data
mysql -u root -p lean4_ai_app -e "SELECT COUNT(*) FROM theorems;"

# If 0, re-import:
mysql -u root -p lean4_ai_app < database/seed_theorems.sql
```

### ❌ "API returns 500 error"
```bash
# Check PHP error log
# Look at browser console for details

# Common issues:
# - Wrong database credentials
# - Missing PHP extensions (php-mysql, php-curl)
# - File permissions
```

### ❌ "DeepSeek API not working"
```bash
# Check API key is set in backend/config/deepseek.php
# Verify key is valid at platform.deepseek.com
# Check internet connection
# Note: Some features work without API key
```

---

## Directory Structure

```
lean4-ai-web-app/
├── START_SYSTEM.bat          ← Run this (Windows)
├── start_system.sh            ← Run this (Linux/Mac)
├── HOW_TO_RUN.md             ← You are here
├── database/
│   ├── schema.sql            ← Database structure
│   ├── seed_theorems.sql     ← Theorem data
│   └── TEST_AND_VERIFY.sql   ← Testing queries
├── backend/
│   ├── config/
│   │   ├── database.php      ← Configure this
│   │   └── deepseek.php      ← Add API key here
│   └── api/
│       ├── theorems.php      ← Theorem API
│       └── proof.php         ← Proof API
└── frontend/
    └── pages/
        ├── theorems.html     ← Start here
        ├── tutor.html
        └── dashboard.html
```

---

## Production Deployment

### Using Apache

**1. Setup Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName reana.local
    DocumentRoot "C:/Project/v11"
    
    <Directory "C:/Project/v11">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**2. Add to hosts file:**
```
127.0.0.1  reana.local
```

**3. Access:** `http://reana.local/frontend/pages/theorems.html`

### Using Docker (Advanced)

Create `Dockerfile`:
```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
EXPOSE 80
```

```bash
docker build -t reana-system .
docker run -p 8080:80 reana-system
```

---

## Next Steps

After running the system:

1. ✅ **Browse Theorems** - Check all 50+ theorems loaded
2. ✅ **Test Search** - Search for "convergence" or "limit"
3. ✅ **Try Proving** - Select a beginner theorem and prove it
4. ✅ **Check Results** - View your proof attempts and scores
5. ✅ **Explore API** - Test endpoints with Postman or curl

---

## Support

Need help?

- 📖 Check `REANA_COMPLETE_SYSTEM.md` for full documentation
- 🧪 Run `database/TEST_AND_VERIFY.sql` for diagnostics
- 🐛 Check browser console for JavaScript errors
- 📊 Check PHP error logs for backend issues

**Common Commands:**
```bash
# View PHP logs
php -S localhost:8080 2>&1 | tee server.log

# Check database
mysql -u root -p lean4_ai_app -e "SHOW TABLES;"

# Test API
curl http://localhost:8080/backend/api/theorems.php?action=get_categories
```

---

**🎉 System ready! Start proving theorems! 🚀**
