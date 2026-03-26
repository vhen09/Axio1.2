# ============================================================================
# REANA System - Windows Setup and Run Script (PostgreSQL Version)
# ============================================================================
# This script sets up and runs the Lean4 AI Web App with PostgreSQL
# IMPORTANT: Run this script as Administrator
# ============================================================================

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")
if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Please right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    pause
    exit 1
}

Write-Host ""
Write-Host "========================================"
Write-Host "  REANA System Setup (PostgreSQL)"
Write-Host "========================================"
Write-Host ""

# ============================================================================
# STEP 1: Check Prerequisites
# ============================================================================
Write-Host "[1/6] Checking prerequisites..." -ForegroundColor Cyan
Write-Host ""

# Check PHP
$phpVersion = php --version 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "[X] PHP not found!" -ForegroundColor Red
    Write-Host "    Download from: https://windows.php.net/download/" -ForegroundColor Yellow
    pause
    exit 1
} else {
    Write-Host "[OK] PHP installed" -ForegroundColor Green
    $phpVersion | Select-Object -First 1
}

Write-Host ""

# Check PostgreSQL
$psqlPath = "C:\Program Files\PostgreSQL\16\bin\psql"
if (-not (Test-Path $psqlPath)) {
    Write-Host "[X] PostgreSQL not found at $psqlPath" -ForegroundColor Red
    Write-Host "    Please install PostgreSQL 16 from: https://www.postgresql.org/download/windows/" -ForegroundColor Yellow
    Write-Host "    Or run: choco install postgresql" -ForegroundColor Yellow
    pause
    exit 1
} else {
    Write-Host "[OK] PostgreSQL installed" -ForegroundColor Green
    & $psqlPath --version
}

Write-Host ""

# ============================================================================
# STEP 2: Setup PostgreSQL PATH
# ============================================================================
Write-Host "[2/6] Setting up PostgreSQL PATH..." -ForegroundColor Cyan
$env:PATH += ";C:\Program Files\PostgreSQL\16\bin"
Write-Host "[OK] PostgreSQL added to PATH" -ForegroundColor Green
Write-Host ""

# ============================================================================
# STEP 3: Create Database and User
# ============================================================================
Write-Host "[3/6] Setting up PostgreSQL database..." -ForegroundColor Cyan
Write-Host ""

try {
    # Check if database exists
    $dbCheck = & $psqlPath -U postgres -t -c "SELECT 1 FROM pg_database WHERE datname='lean4_ai_db';" 2>$null
    
    if (-not $dbCheck) {
        Write-Host "Creating database 'lean4_ai_db'..." -ForegroundColor Yellow
        & $psqlPath -U postgres -c "CREATE DATABASE lean4_ai_db;" 2>$null
        Write-Host "[OK] Database created" -ForegroundColor Green
    } else {
        Write-Host "[OK] Database 'lean4_ai_db' already exists" -ForegroundColor Green
    }
    
    # Check if user exists
    $userCheck = & $psqlPath -U postgres -t -c "SELECT 1 FROM pg_user WHERE usename='lean4_user';" 2>$null
    
    if (-not $userCheck) {
        Write-Host "Creating user 'lean4_user'..." -ForegroundColor Yellow
        & $psqlPath -U postgres -c "CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';" 2>$null
        Write-Host "[OK] User created" -ForegroundColor Green
    } else {
        Write-Host "[OK] User 'lean4_user' already exists" -ForegroundColor Green
    }
    
    # Grant privileges
    Write-Host "Setting user privileges..." -ForegroundColor Yellow
    & $psqlPath -U postgres -c "GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;" 2>$null
    Write-Host "[OK] Privileges granted" -ForegroundColor Green
    
} catch {
    Write-Host "[ERROR] Failed to setup database: $_" -ForegroundColor Red
    pause
    exit 1
}
Write-Host ""

# ============================================================================
# STEP 4: Import Schema
# ============================================================================
Write-Host "[4/6] Importing PostgreSQL schema..." -ForegroundColor Cyan
Write-Host ""

$schemaFile = ".\database\postgres_schema.sql"
if (-not (Test-Path $schemaFile)) {
    Write-Host "[X] Schema file not found: $schemaFile" -ForegroundColor Red
    pause
    exit 1
}

try {
    # Import schema
    & $psqlPath -U lean4_user -d lean4_ai_db -f $schemaFile 2>&1 | ForEach-Object {
        if ($_ -match "ERROR") {
            Write-Host $_ -ForegroundColor Red
        } elseif ($_ -match "CREATE") {
            Write-Host $_ -ForegroundColor Green
        }
    }
    Write-Host "[OK] Schema imported successfully" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to import schema: $_" -ForegroundColor Red
    pause
    exit 1
}
Write-Host ""

# ============================================================================
# STEP 5: Check Database Configuration
# ============================================================================
Write-Host "[5/6] Verifying database configuration..." -ForegroundColor Cyan
Write-Host ""

# Check backend config file
$configFile = ".\backend\config\database.php"
if (Test-Path $configFile) {
    $configContent = Get-Content $configFile -Raw
    
    if ($configContent -match "postgresql") {
        Write-Host "[OK] Database config is set to PostgreSQL" -ForegroundColor Green
    } else {
        Write-Host "[!] Warning: Config may not be set to PostgreSQL" -ForegroundColor Yellow
        Write-Host "    Edit $configFile and ensure:" -ForegroundColor Yellow
        Write-Host "    - dbType = 'postgresql'" -ForegroundColor Yellow
        Write-Host "    - user = 'lean4_user'" -ForegroundColor Yellow
        Write-Host "    - pass = 'lean4_password_123'" -ForegroundColor Yellow
    }
} else {
    Write-Host "[!] Config file not found: $configFile" -ForegroundColor Yellow
}
Write-Host ""

# ============================================================================
# STEP 6: Start PHP Development Server
# ============================================================================
Write-Host "[6/6] Starting PHP development server..." -ForegroundColor Cyan
Write-Host ""

Write-Host "Server will run on: http://localhost:8080" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start PHP server
php -S localhost:8080

Write-Host ""
Write-Host "Server stopped." -ForegroundColor Yellow
