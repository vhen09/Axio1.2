@echo off
REM ============================================================================
REM REANA System - Windows Setup and Run Script
REM ============================================================================

REM Add XAMPP to PATH
set PATH=C:\xampp\mysql\bin;C:\xampp\php;%PATH%

echo.
echo ========================================
echo   REANA System Setup and Run
echo ========================================
echo.

REM Check if running from correct directory
if not exist "backend\api\theorems.php" (
    echo ERROR: Please run this script from the lean4-ai-web-app directory
    pause
    exit /b 1
)

REM ============================================================================
REM STEP 1: Check Prerequisites
REM ============================================================================

echo [1/6] Checking prerequisites...
echo.

REM Check PHP
php --version >nul 2>&1
if errorlevel 1 (
    echo [X] PHP not found! Please install PHP 7.4 or higher
    echo     Download from: https://windows.php.net/download/
    pause
    exit /b 1
) else (
    echo [OK] PHP installed
    php --version | findstr /C:"PHP"
)

REM Check MySQL
mysql --version >nul 2>&1
if errorlevel 1 (
    echo [X] MySQL not found! Please install MySQL or XAMPP
    echo     Download XAMPP from: https://www.apachefriends.org/
    pause
    exit /b 1
) else (
    echo [OK] MySQL installed
    mysql --version
)

echo.

REM ============================================================================
REM STEP 2: Setup Configuration
REM ============================================================================

echo [2/6] Setting up configuration...
echo.

REM Check if database config exists
if not exist "backend\config\database.php" (
    echo Creating database configuration...
    (
        echo ^<?php
        echo.
        echo class Database {
        echo     private $host = "localhost";
        echo     private $db_name = "lean4_ai_app";
        echo     private $username = "root";
        echo     private $password = "";
        echo     private $conn;
        echo.
        echo     public function getConnection^(^) {
        echo         $this-^>conn = null;
        echo         try {
        echo             $this-^>conn = new PDO^(
        echo                 "mysql:host=" . $this-^>host . ";dbname=" . $this-^>db_name,
        echo                 $this-^>username,
        echo                 $this-^>password
        echo             ^);
        echo             $this-^>conn-^>setAttribute^(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION^);
        echo         } catch^(PDOException $e^) {
        echo             echo "Connection Error: " . $e-^>getMessage^(^);
        echo         }
        echo         return $this-^>conn;
        echo     }
        echo }
    ) > backend\config\database.php
    echo [OK] Database config created
) else (
    echo [OK] Database config exists
)

REM Check if DeepSeek config exists
if not exist "backend\config\deepseek.php" (
    echo.
    echo IMPORTANT: You need to configure DeepSeek API key
    echo Please edit backend\config\deepseek.php and add your API key
    echo.
    (
        echo ^<?php
        echo return [
        echo     'deepseek_api_url' =^> 'https://api.deepseek.com/v1/chat/completions',
        echo     'deepseek_api_key' =^> 'YOUR_API_KEY_HERE',  // Get from https://platform.deepseek.com/
        echo     'model' =^> 'deepseek-chat',
        echo     'max_tokens' =^> 3000,
        echo     'temperature' =^> 0.3
        echo ];
    ) > backend\config\deepseek.php
    echo [!] DeepSeek config created - PLEASE ADD YOUR API KEY
) else (
    echo [OK] DeepSeek config exists
)

echo.

REM ============================================================================
REM STEP 3: Setup Database
REM ============================================================================

echo [3/6] Setting up database...
echo.

set /p db_user="Enter MySQL username (default: root): "
if "%db_user%"=="" set db_user=root

set /p db_pass="Enter MySQL password (press Enter if none): "

echo.
echo Creating database and tables...
echo.

mysql -u %db_user% -p%db_pass% -e "CREATE DATABASE IF NOT EXISTS lean4_ai_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo [X] Failed to create database. Please check MySQL credentials.
    pause
    exit /b 1
)

mysql -u %db_user% -p%db_pass% lean4_ai_app < database\schema.sql 2>nul
if errorlevel 1 (
    echo [X] Failed to create tables
    pause
    exit /b 1
) else (
    echo [OK] Database tables created
)

mysql -u %db_user% -p%db_pass% lean4_ai_app < database\seed_theorems.sql 2>nul
if errorlevel 1 (
    echo [X] Failed to seed theorem data
    pause
    exit /b 1
) else (
    echo [OK] Theorem data loaded
)

echo.

REM ============================================================================
REM STEP 4: Verify Installation
REM ============================================================================

echo [4/6] Verifying installation...
echo.

mysql -u %db_user% -p%db_pass% lean4_ai_app -e "SELECT COUNT(*) as theorem_count FROM theorems;" 2>nul
if errorlevel 1 (
    echo [X] Verification failed
) else (
    echo [OK] Database verification successful
)

echo.

REM ============================================================================
REM STEP 5: Start PHP Development Server
REM ============================================================================

echo [5/6] Starting PHP development server...
echo.

set PORT=8080
echo Server will run at: http://localhost:%PORT%
echo.

REM Check if port is available
netstat -an | find ":%PORT%" >nul
if not errorlevel 1 (
    echo [!] Port %PORT% is already in use. Using port 8081 instead...
    set PORT=8081
)

echo.
echo Starting server on port %PORT%...
echo.
echo ========================================
echo   Server is running!
echo ========================================
echo.
echo   Main Pages:
echo   - Theorem Browser:  http://localhost:%PORT%/frontend/pages/theorems.html
echo   - Proof Assistant:  http://localhost:%PORT%/frontend/pages/tutor.html
echo   - Dashboard:        http://localhost:%PORT%/frontend/pages/dashboard.html
echo.
echo   API Endpoints:
echo   - Theorems API:     http://localhost:%PORT%/backend/api/theorems.php
echo   - Proof API:        http://localhost:%PORT%/backend/api/proof.php
echo.
echo   Press Ctrl+C to stop the server
echo ========================================
echo.

REM Start PHP server
cd /d "%~dp0"
php -S localhost:%PORT%

pause
