@echo off
REM PostgreSQL Setup Script for Windows
REM Automated migration and verification

echo.
echo ════════════════════════════════════════════════════════════════
echo   PostgreSQL Migration Setup
echo ════════════════════════════════════════════════════════════════
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP or add it to Windows PATH
    pause
    exit /b 1
)

echo Step 1/3: Running PostgreSQL setup verification...
echo.

cd /d "%~dp0"
php setup-postgres.php

echo.
echo ════════════════════════════════════════════════════════════════
echo   Setup Complete
echo ════════════════════════════════════════════════════════════════
echo.

pause
