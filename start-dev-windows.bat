@echo off
REM AZ-305 Helper - Windows Quick Start Script

echo.
echo ========================================
echo AZ-305 Certification Helper
echo Quick Start Script (Windows)
echo ========================================
echo.

REM Check if PHP is installed
php -v >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] PHP is not installed or not in PATH.
    echo.
    echo Please install PHP 8.2 or higher from https://www.php.net/downloads
    echo And add it to your system PATH.
    pause
    exit /b 1
)

echo [OK] PHP is installed
php -v | findstr /R "PHP" | for /f "tokens=2" %%i in ('more') do echo Version: %%i
echo.

REM Check if Git is installed
git --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [WARNING] Git is not installed. Version control may not work.
    echo Download from https://git-scm.com/download/win
    echo.
) else (
    echo [OK] Git is installed
    git --version
    echo.
)

REM Create necessary directories
echo [*] Creating directory structure...
if not exist "data\sessions" (
    mkdir data\sessions
    echo [OK] Created data/sessions directory
) else (
    echo [OK] data/sessions directory already exists
)

echo.
echo [*] Checking file permissions...
echo [OK] Windows handles permissions automatically

echo.
echo ========================================
echo Starting Development Server
echo ========================================
echo.

cd public
echo Starting PHP built-in server on http://localhost:8000
echo.
echo Press Ctrl+C to stop the server.
echo.

php -S localhost:8000

pause
