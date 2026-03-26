#!/bin/bash

# ============================================================================
# REANA System - Linux/Mac Setup and Run Script
# ============================================================================

echo ""
echo "========================================"
echo "   REANA System Setup and Run"
echo "========================================"
echo ""

# Check if running from correct directory
if [ ! -f "backend/api/theorems.php" ]; then
    echo "ERROR: Please run this script from the lean4-ai-web-app directory"
    exit 1
fi

# ============================================================================
# STEP 1: Check Prerequisites
# ============================================================================

echo "[1/6] Checking prerequisites..."
echo ""

# Check PHP
if ! command -v php &> /dev/null; then
    echo "[X] PHP not found! Please install PHP 7.4 or higher"
    echo "    Ubuntu/Debian: sudo apt-get install php php-mysql php-curl"
    echo "    Mac: brew install php"
    exit 1
else
    echo "[OK] PHP installed"
    php --version | head -n 1
fi

# Check MySQL
if ! command -v mysql &> /dev/null; then
    echo "[X] MySQL not found! Please install MySQL"
    echo "    Ubuntu/Debian: sudo apt-get install mysql-server"
    echo "    Mac: brew install mysql"
    exit 1
else
    echo "[OK] MySQL installed"
    mysql --version
fi

echo ""

# ============================================================================
# STEP 2: Setup Configuration
# ============================================================================

echo "[2/6] Setting up configuration..."
echo ""

# Check if database config exists
if [ ! -f "backend/config/database.php" ]; then
    echo "Creating database configuration..."
    cat > backend/config/database.php << 'EOF'
<?php

class Database {
    private $host = "localhost";
    private $db_name = "lean4_ai_app";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
EOF
    echo "[OK] Database config created"
else
    echo "[OK] Database config exists"
fi

# Check if DeepSeek config exists
if [ ! -f "backend/config/deepseek.php" ]; then
    echo ""
    echo "IMPORTANT: You need to configure DeepSeek API key"
    echo "Please edit backend/config/deepseek.php and add your API key"
    echo ""
    cat > backend/config/deepseek.php << 'EOF'
<?php
return [
    'deepseek_api_url' => 'https://api.deepseek.com/v1/chat/completions',
    'deepseek_api_key' => 'YOUR_API_KEY_HERE',  // Get from https://platform.deepseek.com/
    'model' => 'deepseek-chat',
    'max_tokens' => 3000,
    'temperature' => 0.3
];
EOF
    echo "[!] DeepSeek config created - PLEASE ADD YOUR API KEY"
else
    echo "[OK] DeepSeek config exists"
fi

echo ""

# ============================================================================
# STEP 3: Setup Database
# ============================================================================

echo "[3/6] Setting up database..."
echo ""

read -p "Enter MySQL username (default: root): " db_user
db_user=${db_user:-root}

read -sp "Enter MySQL password (press Enter if none): " db_pass
echo ""
echo ""

echo "Creating database and tables..."
echo ""

# Create database
mysql -u "$db_user" -p"$db_pass" -e "CREATE DATABASE IF NOT EXISTS lean4_ai_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "[X] Failed to create database. Please check MySQL credentials."
    exit 1
fi

# Create tables
mysql -u "$db_user" -p"$db_pass" lean4_ai_app < database/schema.sql 2>/dev/null
if [ $? -ne 0 ]; then
    echo "[X] Failed to create tables"
    exit 1
else
    echo "[OK] Database tables created"
fi

# Seed theorems
mysql -u "$db_user" -p"$db_pass" lean4_ai_app < database/seed_theorems.sql 2>/dev/null
if [ $? -ne 0 ]; then
    echo "[X] Failed to seed theorem data"
    exit 1
else
    echo "[OK] Theorem data loaded"
fi

echo ""

# ============================================================================
# STEP 4: Verify Installation
# ============================================================================

echo "[4/6] Verifying installation..."
echo ""

mysql -u "$db_user" -p"$db_pass" lean4_ai_app -e "SELECT COUNT(*) as theorem_count FROM theorems;" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "[X] Verification failed"
else
    echo "[OK] Database verification successful"
fi

echo ""

# ============================================================================
# STEP 5: Start PHP Development Server
# ============================================================================

echo "[5/6] Starting PHP development server..."
echo ""

PORT=8080
echo "Server will run at: http://localhost:$PORT"
echo ""

# Check if port is available
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo "[!] Port $PORT is already in use. Using port 8081 instead..."
    PORT=8081
fi

echo ""
echo "Starting server on port $PORT..."
echo ""
echo "========================================"
echo "   Server is running!"
echo "========================================"
echo ""
echo "   Main Pages:"
echo "   - Theorem Browser:  http://localhost:$PORT/frontend/pages/theorems.html"
echo "   - Proof Assistant:  http://localhost:$PORT/frontend/pages/tutor.html"
echo "   - Dashboard:        http://localhost:$PORT/frontend/pages/dashboard.html"
echo ""
echo "   API Endpoints:"
echo "   - Theorems API:     http://localhost:$PORT/backend/api/theorems.php"
echo "   - Proof API:        http://localhost:$PORT/backend/api/proof.php"
echo ""
echo "   Press Ctrl+C to stop the server"
echo "========================================"
echo ""

# Start PHP server
php -S localhost:$PORT
