# PostgreSQL Setup Script
$psqlPath = "C:\Program Files\PostgreSQL\18\bin\psql"
$pgPassword = "tkdvhen1021"
$env:PGPASSWORD = $pgPassword

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "PostgreSQL Database Setup" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Test connection
Write-Host "[1/4] Testing PostgreSQL connection..." -ForegroundColor Yellow
try {
    $result = & $psqlPath -U postgres -h localhost -c "SELECT version();" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] PostgreSQL is running" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Cannot connect to PostgreSQL" -ForegroundColor Red
        Write-Host "Make sure PostgreSQL service is running: net start postgresql-x64-16" -ForegroundColor Yellow
        exit 1
    }
} catch {
    Write-Host "[ERROR] $_" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Step 2: Create database
Write-Host "[2/4] Creating database lean4_ai_db..." -ForegroundColor Yellow
& $psqlPath -U postgres -h localhost -c "DROP DATABASE IF EXISTS lean4_ai_db;" 2>&1 | Out-Null
& $psqlPath -U postgres -h localhost -c "CREATE DATABASE lean4_ai_db;" 2>&1 | Out-Null
Write-Host "[OK] Database created" -ForegroundColor Green
Write-Host ""

# Step 3: Create user and set permissions
Write-Host "[3/4] Creating user and setting permissions..." -ForegroundColor Yellow
& $psqlPath -U postgres -h localhost -c "DROP ROLE IF EXISTS lean4_user;" 2>&1 | Out-Null
& $psqlPath -U postgres -h localhost -c "CREATE USER lean4_user WITH PASSWORD 'lean4_password_123';" 2>&1 | Out-Null
& $psqlPath -U postgres -h localhost -c "GRANT ALL PRIVILEGES ON DATABASE lean4_ai_db TO lean4_user;" 2>&1 | Out-Null
Write-Host "[OK] User created and permissions granted" -ForegroundColor Green
Write-Host ""

# Step 4: Import schema
Write-Host "[4/4] Importing database schema..." -ForegroundColor Yellow
$schemaPath = "C:\Project\v11\database\postgres_schema.sql"

if (Test-Path $schemaPath) {
    $env:PGPASSWORD = "lean4_password_123"
    & $psqlPath -U lean4_user -h localhost -d lean4_ai_db -f $schemaPath 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Schema imported successfully" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Failed to import schema" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "[ERROR] Schema file not found: $schemaPath" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Green
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Database credentials:" -ForegroundColor Cyan
Write-Host "  Host: localhost" -ForegroundColor Gray
Write-Host "  Port: 5432" -ForegroundColor Gray
Write-Host "  Database: lean4_ai_db" -ForegroundColor Gray
Write-Host "  User: lean4_user" -ForegroundColor Gray
Write-Host "  Password: lean4_password_123" -ForegroundColor Gray
Write-Host ""
Write-Host "Starting PHP development server..." -ForegroundColor Yellow
Write-Host "Server will run on: http://localhost:8080" -ForegroundColor Green
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Start PHP server
Set-Location C:\Project\v11
php -S localhost:8080
