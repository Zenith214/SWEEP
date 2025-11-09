# SWEEP - Quick Start Guide

Get SWEEP up and running in minutes with our automated setup scripts!

## Prerequisites

Before running the setup script, ensure you have:

- **PHP 8.2+** installed
- **Composer** installed
- **Node.js 18+** and npm installed
- **MySQL/MariaDB** installed and running
- **Git** installed (to clone the repository)

## Installation Methods

### Method 1: Automated Setup (Recommended)

#### For Linux/macOS:

```bash
# Clone the repository
git clone <repository-url>
cd sweep

# Make the setup script executable
chmod +x setup.sh

# Run the setup script
./setup.sh
```

#### For Windows:

```cmd
# Clone the repository
git clone <repository-url>
cd sweep

# Run the setup script
setup.bat
```

The automated script will:
- ✓ Check all system requirements
- ✓ Verify PHP extensions
- ✓ Install PHP dependencies (Composer)
- ✓ Install JavaScript dependencies (npm)
- ✓ Create .env file
- ✓ Generate application key
- ✓ Run database migrations (optional)
- ✓ Seed sample data (optional)
- ✓ Create storage link
- ✓ Build frontend assets (optional)

### Method 2: Manual Setup

If you prefer manual installation or the script doesn't work:

```bash
# 1. Clone repository
git clone <repository-url>
cd sweep

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env file
# Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Run migrations
php artisan migrate

# 6. Seed database (optional)
php artisan db:seed

# 7. Create storage link
php artisan storage:link

# 8. Build assets
npm run build
```

### Method 3: One-Line Composer Setup

```bash
# Clone and run composer setup
git clone <repository-url> && cd sweep && composer setup
```

This runs the composer setup script which handles most installation steps.

## Database Configuration

Before running migrations, configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sweep
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Create Database

```sql
-- MySQL/MariaDB
CREATE DATABASE sweep CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Starting the Application

### Development Mode

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server (for hot reload)
npm run dev
```

Visit: `http://localhost:8000`

### Using Composer Dev Script

```bash
# Runs server, queue worker, and Vite simultaneously
composer dev
```

## Default Login Credentials

After seeding the database:

### Administrator
- **Email**: `admin@sweep.local`
- **Password**: `ChangeMe123!`
- **Access**: Full system administration

### Collection Crew
- **Email**: `john.smith@sweep.local`
- **Password**: `password`
- **Access**: Route assignments and collection logging

### Resident
- **Email**: `john.doe@example.com`
- **Password**: `password`
- **Access**: View schedules and submit reports

> **Security Warning**: Change these passwords immediately in production!

## Troubleshooting

### Common Issues

#### 1. PHP Extensions Missing

**Error**: "Extension not found"

**Solution**:
```bash
# Ubuntu/Debian
sudo apt-get install php8.2-bcmath php8.2-curl php8.2-gd php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip

# macOS (Homebrew)
brew install php@8.2

# Windows
# Edit php.ini and uncomment extension lines
```

#### 2. Permission Errors

**Error**: "Permission denied" on storage or cache

**Solution**:
```bash
# Linux/macOS
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or use your web server user
chown -R $USER:www-data storage bootstrap/cache
```

#### 3. Database Connection Failed

**Error**: "SQLSTATE[HY000] [2002] Connection refused"

**Solution**:
- Verify MySQL/MariaDB is running
- Check database credentials in `.env`
- Ensure database exists
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

#### 4. npm Install Fails

**Error**: "EACCES: permission denied"

**Solution**:
```bash
# Fix npm permissions
sudo chown -R $USER:$GROUP ~/.npm
sudo chown -R $USER:$GROUP ~/.config

# Or use nvm (recommended)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
```

#### 5. Composer Install Fails

**Error**: "Your requirements could not be resolved"

**Solution**:
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Try again
composer install
```

#### 6. Assets Not Loading

**Error**: Blank page or missing styles

**Solution**:
```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Recreate storage link
php artisan storage:link
```

## Verification Steps

After installation, verify everything works:

### 1. Check Application
```bash
php artisan about
```

### 2. Run Tests
```bash
php artisan test
```

### 3. Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### 4. Verify Queue System
```bash
php artisan queue:work --once
```

### 5. Check Storage
```bash
ls -la storage/app/public
ls -la public/storage
```

## Next Steps

1. **Configure Email** (optional)
   - Edit mail settings in `.env`
   - Test: `php artisan tinker` then `Mail::raw('Test', function($m) { $m->to('test@example.com')->subject('Test'); });`

2. **Set Up Queue Worker** (production)
   - Configure supervisor or systemd
   - See: `docs/DEPLOYMENT.md`

3. **Configure Caching** (production)
   - Install Redis
   - Update `CACHE_DRIVER=redis` in `.env`

4. **Review Security Settings**
   - See: `SECURITY.md`
   - Change default passwords
   - Configure session settings

5. **Customize Application**
   - Update `APP_NAME` in `.env`
   - Customize colors in Tailwind config
   - Add your logo

## Development Workflow

```bash
# Start development environment
composer dev

# Or manually:
# Terminal 1
php artisan serve

# Terminal 2
php artisan queue:work

# Terminal 3
npm run dev
```

## Production Deployment

For production deployment, see:
- `README.md` - Complete documentation
- `SECURITY.md` - Security configuration
- `.env.production.example` - Production environment template

## Getting Help

- **Documentation**: See `docs/` directory
- **Issues**: Check GitHub issues
- **Logs**: Check `storage/logs/laravel.log`

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database
php artisan migrate:fresh --seed  # Reset database
php artisan db:seed --class=AdminUserSeeder  # Seed specific seeder

# Queue
php artisan queue:work  # Process jobs
php artisan queue:failed  # List failed jobs
php artisan queue:retry all  # Retry failed jobs

# Maintenance
php artisan down  # Enable maintenance mode
php artisan up  # Disable maintenance mode

# Generate IDE helper (optional)
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
```

## System Requirements Recap

- PHP 8.2+
- Composer 2.x+
- Node.js 18+
- npm 9+
- MySQL 8.0+ or MariaDB 10.6+
- 2GB RAM (4GB recommended)
- 1GB disk space

## Success!

If you see the SWEEP landing page at `http://localhost:8000`, you're all set! 🎉

Log in with one of the default accounts and start exploring the system.

---

**Need Help?** Check the full documentation in `README.md` or review the troubleshooting section above.
