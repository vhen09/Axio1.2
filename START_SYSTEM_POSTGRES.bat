@echo off
REM ============================================================================
REM REANA System - Windows Launcher for PostgreSQL Setup
REM ============================================================================
REM This batch file launches the PowerShell setup script with admin privileges
REM ============================================================================

echo.
echo ========================================
echo   REANA System - PostgreSQL Launcher
echo ========================================
echo.

REM Check if running as Administrator
openfiles >nul 2>&1
if errorlevel 1 (
    echo ERROR: This script must be run as Administrator!
    echo.
    echo Please right-click this file and select:
    echo   "Run as Administrator"
    echo.
    pause
    exit /b 1
)

REM Check if PowerShell script exists
if not exist "START_SYSTEM_POSTGRES.ps1" (
    echo ERROR: START_SYSTEM_POSTGRES.ps1 not found!
    echo Please run this batch file from C:\Project\v11
    echo.
    pause
    exit /b 1
)

REM Run PowerShell script
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "START_SYSTEM_POSTGRES.ps1"

pause
