#!/usr/bin/env powershell
<#
PostgreSQL Complete Setup Script for Windows
Automated setup for Real Analysis Theorem Proving System
Run as Administrator in PowerShell
#>

# Check if running as Administrator
$isAdmin = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($isAdmin)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host "ERROR: This script must run as Administrator!" -ForegroundColor Red
    Write-Host "Please right-click PowerShell and select 'Run as administrator'" -ForegroundColor Yellow
    exit 1
}

# Colors for output
$Success = @{ForegroundColor = 'Green'}
$Error = @{ForegroundColor = 'Red'}
$Warning = @{ForegroundColor = 'Yellow'}
$Info = @{ForegroundColor = 'Cyan'}

Clear-Host

Write-Host "`n" -ForegroundColor White
Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  PostgreSQL Setup - Real Analysis Theorem Proving System       ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# ============================================================================
# STEP 1: Verify PostgreSQL Installation
# ============================================================================

Write-Host "STEP 1/6: Checking PostgreSQL Installation" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

# Try to find PostgreSQL
$psqlPath = "C:\Program Files\PostgreSQL\16\bin\psql.exe"
$pgVersions = @(16, 17, 15, 14)  # Try these versions

$found = $false
foreach ($ver in $pgVersions) {
    $testPath = "C:\Program Files\PostgreSQL\$ver\bin\psql.exe"
    if (Test-Path $testPath) {
        $psqlPath = $testPath
        Write-Host "✓ PostgreSQL $ver found at: $testPath" @Success
        $found = $true
        break
    }
}

if (-not $found) {
    Write-Host "✗ PostgreSQL not found!" @Error
    Write-Host "`nPlease download and install PostgreSQL from:" @Warning
    Write-Host "https://www.postgresql.org/download/windows/" -ForegroundColor Blue
    Write-Host "`nThen re-run this script.`n" @Warning
    exit 1
}

# ============================================================================
# STEP 2: Add PostgreSQL to PATH
# ============================================================================

Write-Host "`nSTEP 2/6: Adding PostgreSQL to PATH" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

$pgBinPath = Split-Path $psqlPath
$env:PATH += ";$pgBinPath"

Write-Host "✓ Added to PATH: $pgBinPath" @Success

# ============================================================================
# STEP 3: Test PostgreSQL Connection
# ============================================================================

Write-Host "`nSTEP 3/6: Testing PostgreSQL Server Connection" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

try {
    $output = & $psqlPath -U postgres -c "SELECT version();" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ PostgreSQL server is running" @Success
        Write-Host "  Version: $(($output[2]) -replace "^.*version\s+", "")" @Success
    } else {
        Write-Host "⚠ PostgreSQL server might not be running" @Warning
        Write-Host "  Starting PostgreSQL service..." @Info
        net start postgresql-x64-16 2>&1 | Out-Null
        Start-Sleep 3
    }
} catch {
    Write-Host "⚠ Could not connect to PostgreSQL" @Warning
    Write-Host "  Will continue with database creation..." @Info
}

# ============================================================================
# STEP 4: Create Database and User
# ============================================================================

Write-Host "`nSTEP 4/6: Creating Database and User" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

$dbName = "lean4_ai_db"
$userName = "lean4_user"
$userPassword = "lean4_password_123"

# Check if database already exists
try {
    $output = & $psqlPath -U postgres -d postgres -c "SELECT 1 FROM pg_database WHERE datname = '$dbName';" 2>&1
    if ($output -contains "1 row") {
        Write-Host "✓ Database '$dbName' already exists" @Success
    } else {
        Write-Host "  Creating database '$dbName'..." @Info
        & $psqlPath -U postgres -c "CREATE DATABASE $dbName;" 2>&1 | Out-Null
        Write-Host "✓ Database created: $dbName" @Success
    }
} catch {
    Write-Host "⚠ Database check failed: $_" @Warning
}

# Create user
try {
    $output = & $psqlPath -U postgres -d postgres -c "SELECT 1 FROM pg_user WHERE usename = '$userName';" 2>&1
    if ($output -contains "1 row") {
        Write-Host "✓ User '$userName' already exists" @Success
    } else {
        Write-Host "  Creating user '$userName'..." @Info
        & $psqlPath -U postgres -d $dbName -c "CREATE USER $userName WITH PASSWORD '$userPassword';" 2>&1 | Out-Null
        Write-Host "✓ User created: $userName" @Success
    }
} catch {
    Write-Host "  Creating user '$userName'..." @Info
    & $psqlPath -U postgres -d $dbName -c "CREATE USER $userName WITH PASSWORD '$userPassword';" 2>&1 | Out-Null
    Write-Host "✓ User created: $userName" @Success
}

# Grant privileges
Write-Host "  Granting privileges..." @Info
& $psqlPath -U postgres -d $dbName -c "GRANT ALL PRIVILEGES ON DATABASE $dbName TO $userName;" 2>&1 | Out-Null
Write-Host "✓ Privileges granted" @Success

# ============================================================================
# STEP 5: Import Schema
# ============================================================================

Write-Host "`nSTEP 5/6: Importing PostgreSQL Schema" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

$schemaFile = "C:\Project\v11\database\postgres_schema.sql"

if (-not (Test-Path $schemaFile)) {
    Write-Host "✗ Schema file not found: $schemaFile" @Error
    exit 1
}

Write-Host "  Importing schema from: $schemaFile" @Info

try {
    $env:PGPASSWORD = $userPassword
    & $psqlPath -U $userName -d $dbName -f $schemaFile 2>&1 | ForEach-Object {
        if ($_ -match "CREATE|ERROR") {
            Write-Host "  $_" @Info
        }
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Schema imported successfully" @Success
    } else {
        Write-Host "⚠ Schema import had issues (might be duplicates)" @Warning
    }
} catch {
    Write-Host "✗ Error importing schema: $_" @Error
    exit 1
} finally {
    $env:PGPASSWORD = ""
}

# ============================================================================
# STEP 6: Verify Setup
# ============================================================================

Write-Host "`nSTEP 6/6: Verifying Setup" @Info
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan

$phpPath = "C:\Project\v11\database\setup-postgres.php"

if (Test-Path $phpPath) {
    Write-Host "  Running PHP verification script..." @Info
    Write-Host ""
    
    try {
        $env:PGPASSWORD = $userPassword
        php $phpPath
        $env:PGPASSWORD = ""
    } catch {
        Write-Host "⚠ Verification script error: $_" @Warning
    }
} else {
    Write-Host "⚠ Verification script not found" @Warning
    Write-Host "  Running manual checks..." @Info
    
    $env:PGPASSWORD = $userPassword
    
    # Check tables
    $result = & $psqlPath -U $userName -d $dbName -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='public';" 2>&1 | Select-Object -Last 1
    Write-Host "  Tables: $result" @Info
    
    # Check theorems
    $result = & $psqlPath -U $userName -d $dbName -c "SELECT COUNT(*) FROM theorems;" 2>&1 | Select-Object -Last 1
    Write-Host "  Theorems: $result" @Info
    
    $env:PGPASSWORD = ""
}

# ============================================================================
# DONE
# ============================================================================

Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║  ✓ SETUP COMPLETE!                                             ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Green

Write-Host "NEXT STEPS:" @Info
Write-Host "1. Start PHP server:" @Info
Write-Host "   php -S localhost:8080" -ForegroundColor Yellow
Write-Host ""
Write-Host "2. Open browser:" @Info
Write-Host "   http://localhost:8080/frontend/pages/theorems.html" -ForegroundColor Yellow
Write-Host ""
Write-Host "3. Test the system:" @Info
Write-Host "   - Browse theorems" -ForegroundColor Yellow
Write-Host "   - Submit a proof" -ForegroundColor Yellow
Write-Host "   - View AI feedback" -ForegroundColor Yellow
Write-Host ""

Write-Host "DATABASE INFO:" @Info
Write-Host "  Host:     localhost" -ForegroundColor Cyan
Write-Host "  Port:     5432" -ForegroundColor Cyan
Write-Host "  Database: lean4_ai_db" -ForegroundColor Cyan
Write-Host "  User:     lean4_user" -ForegroundColor Cyan
Write-Host ""

Write-Host "Documentation:" @Info
Write-Host "  Quick Start:      POSTGRES_QUICKSTART.md" -ForegroundColor Cyan
Write-Host "  Full Guide:       POSTGRESQL_MIGRATION_GUIDE.md" -ForegroundColor Cyan
Write-Host "  System Overview:  HOW_TO_RUN.md" -ForegroundColor Cyan
Write-Host ""

Read-Host "Press Enter to continue"

