@echo off
REM OfisiLink Deployment Script for Windows (Local Testing)
REM This script prepares files for deployment but should be run on the server

echo.
echo ========================================
echo OfisiLink Deployment Preparation
echo ========================================
echo.

REM Check if artisan exists
if not exist "artisan" (
    echo [ERROR] artisan file not found. Are you in the Laravel root directory?
    pause
    exit /b 1
)

echo [OK] Laravel directory detected
echo.

REM Install Composer dependencies
echo [1/6] Installing Composer dependencies (production)...
call composer install --no-dev --optimize-autoloader --no-interaction
if errorlevel 1 (
    echo [ERROR] Composer install failed
    pause
    exit /b 1
)
echo [OK] Dependencies installed
echo.

REM Build frontend assets
echo [2/6] Building frontend assets...
if exist "package.json" (
    call npm install
    call npm run build
    echo [OK] Frontend assets built
) else (
    echo [SKIP] No package.json found
)
echo.

REM Clear caches
echo [3/6] Clearing caches...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
call php artisan view:clear
echo [OK] Caches cleared
echo.

REM Optimize
echo [4/6] Optimizing application...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo [OK] Application optimized
echo.

REM Create storage link
echo [5/6] Creating storage link...
call php artisan storage:link
echo [OK] Storage link created
echo.

echo [6/6] Deployment preparation complete!
echo.
echo ========================================
echo Next Steps:
echo ========================================
echo 1. Upload files to server (via FTP/SFTP)
echo 2. On server: Run deploy.sh script
echo 3. Configure .env file on server
echo 4. Run migrations: php artisan migrate --force
echo.
pause

