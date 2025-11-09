@echo off
REM ============================================================================
REM SWEEP - Setup Script for Windows
REM This script automates the complete installation and setup process
REM ============================================================================

setlocal enabledelayedexpansion

echo.
echo ===============================================================
echo   SWEEP Installation Script for Windows
echo ===============================================================
echo.

REM Check PHP
echo [1/8] Checking PHP...
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP not found in PATH
    echo Please install PHP 8.2 or higher from https://windows.php.net/
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('php -r "echo PHP_VERSION;"') do set PHP_VERSION=%%i
echo [OK] PHP %PHP_VERSION% detected

REM Check PHP version
for /f "tokens=1,2 delims=." %%a in ("%PHP_VERSION%") do (
    set PHP_MAJOR=%%a
    set PHP_MINOR=%%b
)

if %PHP_MAJOR% lss 8 (
    echo [ERROR] PHP 8.2+ required, found %PHP_VERSION%
    pause
    exit /b 1
)

if %PHP_MAJOR% equ 8 if %PHP_MINOR% lss 2 (
    echo [ERROR] PHP 8.2+ required, found %PHP_VERSION%
    pause
    exit /b 1
)

REM Check Composer
echo.
echo [2/8] Checking Composer...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Composer not found in PATH
    echo Please install Composer from https://getcomposer.org/
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('composer --version 2^>nul ^| findstr /R "[0-9]\.[0-9]\.[0-9]"') do set COMPOSER_VERSION=%%i
echo [OK] Composer detected

REM Check Node.js
echo.
echo [3/8] Checking Node.js...
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js not found in PATH
    echo Please install Node.js 18+ from https://nodejs.org/
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('node -v') do set NODE_VERSION=%%i
echo [OK] Node.js %NODE_VERSION% detected

REM Check npm
echo.
echo [4/8] Checking npm...
where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] npm not found in PATH
    pause
    exit /b 1
)

for /f "tokens=*" %%i in ('npm -v') do set NPM_VERSION=%%i
echo [OK] npm %NPM_VERSION% detected

REM Check required PHP extensions
echo.
echo [5/8] Checking PHP extensions...
set MISSING_EXTENSIONS=

php -m | findstr /i "bcmath" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! bcmath
php -m | findstr /i "ctype" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! ctype
php -m | findstr /i "curl" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! curl
php -m | findstr /i "fileinfo" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! fileinfo
php -m | findstr /i "gd" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! gd
php -m | findstr /i "mbstring" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! mbstring
php -m | findstr /i "openssl" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! openssl
php -m | findstr /i "pdo" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! pdo
php -m | findstr /i "pdo_mysql" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! pdo_mysql
php -m | findstr /i "tokenizer" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! tokenizer
php -m | findstr /i "xml" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! xml
php -m | findstr /i "zip" >nul || set MISSING_EXTENSIONS=!MISSING_EXTENSIONS! zip

if not "!MISSING_EXTENSIONS!"=="" (
    echo [ERROR] Missing PHP extensions:!MISSING_EXTENSIONS!
    echo Please enable these extensions in your php.ini file
    pause
    exit /b 1
)

echo [OK] All required PHP extensions found

REM Install PHP dependencies
echo.
echo [6/8] Installing PHP dependencies...
echo Running: composer install
call composer install --no-interaction --prefer-dist --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install PHP dependencies
    pause
    exit /b 1
)
echo [OK] PHP dependencies installed

REM Install JavaScript dependencies
echo.
echo [7/8] Installing JavaScript dependencies...
echo Running: npm install
call npm install
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install JavaScript dependencies
    pause
    exit /b 1
)
echo [OK] JavaScript dependencies installed

REM Setup environment
echo.
echo [8/8] Setting up environment...

if not exist .env (
    echo Creating .env file from .env.example...
    copy .env.example .env >nul
    echo [OK] .env file created
) else (
    echo [WARNING] .env file already exists, skipping
)

REM Generate application key
echo.
echo Generating application key...
php artisan key:generate --ansi
echo [OK] Application key generated

REM Database setup
echo.
set /p MIGRATE="Do you want to run database migrations now? (y/n): "
if /i "%MIGRATE%"=="y" (
    echo.
    echo Running database migrations...
    php artisan migrate --force
    if %errorlevel% neq 0 (
        echo [ERROR] Migration failed. Please check your database configuration in .env
        pause
        exit /b 1
    )
    echo [OK] Database migrations completed
    
    echo.
    set /p SEED="Do you want to seed the database with sample data? (y/n): "
    if /i "!SEED!"=="y" (
        echo.
        echo Seeding database...
        php artisan db:seed --force
        echo [OK] Database seeded
        
        echo.
        echo ===============================================================
        echo   Default Login Credentials
        echo ===============================================================
        echo.
        echo Administrator:
        echo   Email: admin@sweep.local
        echo   Password: ChangeMe123!
        echo.
        echo Collection Crew:
        echo   Email: john.smith@sweep.local
        echo   Password: password
        echo.
        echo Resident:
        echo   Email: john.doe@example.com
        echo   Password: password
        echo.
        echo [WARNING] Change these passwords immediately in production!
        echo.
    )
) else (
    echo [INFO] Skipping database setup
    echo [WARNING] Remember to run 'php artisan migrate' before using the application
)

REM Create storage link
echo.
echo Creating storage link...
php artisan storage:link
echo [OK] Storage link created

REM Build assets
echo.
set /p BUILD="Do you want to build frontend assets now? (y/n): "
if /i "%BUILD%"=="y" (
    echo.
    echo Building assets for production...
    call npm run build
    echo [OK] Assets built
) else (
    echo [INFO] Skipping asset build
    echo [WARNING] Run 'npm run build' for production or 'npm run dev' for development
)

REM Final message
echo.
echo ===============================================================
echo   Installation Complete!
echo ===============================================================
echo.
echo SWEEP has been successfully installed!
echo.
echo Next steps:
echo   1. Configure your database in .env file
echo   2. Run 'php artisan serve' to start the development server
echo   3. Visit http://localhost:8000 in your browser
echo   4. For development with hot reload: 'npm run dev' in another terminal
echo.
echo For production deployment, see README.md
echo.
echo ===============================================================
echo.

pause
