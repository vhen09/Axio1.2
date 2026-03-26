@echo off
REM PostgreSQL Setup Script - Windows Batch Wrapper
REM Runs the PowerShell setup script with correct permissions

cls
color 0A

echo.
echo ════════════════════════════════════════════════════════════════
echo   PostgreSQL Setup for Real Analysis Theorem Proving System
echo ════════════════════════════════════════════════════════════════
echo.

REM Check if running as Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script requires Administrator privileges!
    echo.
    echo Please right-click this file and select "Run as administrator"
    echo.
    pause
    exit /b 1
)

REM Change to database directory
cd /d "%~dp0"

REM Run PowerShell script with correct execution policy
powershell -NoProfile -ExecutionPolicy Bypass -Command "& '%~dp0SETUP-PostgreSQL.ps1'"

if %errorLevel% neq 0 (
    echo.
    echo Setup encountered errors. Please review the output above.
    pause
    exit /b %errorLevel%
)

exit /b 0
