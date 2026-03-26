@echo off
REM Start PostgreSQL if not running
net start postgresql-x64-16 >nul 2>&1

REM Run PowerShell setup script
cd /d C:\Project\v11
powershell -NoProfile -ExecutionPolicy Bypass -File setup-postgres.ps1
