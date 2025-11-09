# SWEEP - Solid Waste Evaluation and Efficiency Platform

A comprehensive web-based waste management system built with Laravel that enables administrators, collection crews, and residents to collaborate efficiently in managing community waste collection operations.

## Overview

SWEEP centralizes waste collection management by providing scheduling, route tracking, reporting, and recycling analytics in a single platform. The system promotes operational efficiency, transparency, and data-driven decision-making for waste management operations.

## Key Features

### Role-Based Access Control
- **Administrator**: Full system management, user administration, route planning, and analytics
- **Collection Crew**: Route assignments, collection logging, and photo documentation
- **Resident**: Schedule viewing, complaint submission, and report tracking

### Core Functionality

#### Waste Collection Management
- Dynamic route planning and scheduling
- Truck and crew assignment system
- Real-time collection logging with photo proof
- GPS-enabled route tracking
- Holiday schedule management

#### Resident Services
- Interactive collection schedule calendar
- Complaint and missed pickup reporting
- Photo upload for issue documentation
- Real-time report status tracking
- SMS/email notifications (configurable)

#### Analytics & Reporting
- Comprehensive admin dashboard with real-time metrics
- Collection performance analytics
- Recycling tracking and target monitoring
- Crew performance metrics
- Geographic distribution analysis
- Exportable reports (PDF, Excel, CSV)
- Scheduled automated reports

#### Advanced Features
- Interactive Chart.js visualizations
- Drill-down navigation for detailed insights
- Accessibility-compliant interface (WCAG 2.1 AA)
- Performance-optimized queries with caching
- Comprehensive error handling and logging
- Mobile-responsive design

## Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MariaDB/MySQL
- **Authentication**: Laravel Breeze
- **Permissions**: Spatie Laravel Permission
- **Queue System**: Database driver (configurable)

### Frontend
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js 3.x
- **Charts**: Chart.js 4.x
- **Calendar**: FullCalendar 6.x
- **Build Tool**: Vite 7.x

### Additional Packages
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel
- **Image Processing**: intervention/image

## System Requirements

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- npm 9.x or higher
- MariaDB 10.6+ or MySQL 8.0+
- 2GB RAM minimum (4GB recommended)
- 1GB disk space minimum

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd sweep
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

Configure your database connection in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sweep
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Run migrations and seeders:

```bash
# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 5. Storage Setup

```bash
# Create symbolic link for storage
php artisan storage:link

# Set proper permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Development Server

```bash
# Option 1: Using Laravel's built-in server
php artisan serve

# Option 2: Using the dev script (includes queue worker and Vite)
composer dev
```

Visit `http://localhost:8000` in your browser.

## Quick Setup Script

For a streamlined setup, use the composer script:

```bash
composer setup
```

This will:
- Install all dependencies
- Copy .env.example to .env
- Generate application key
- Run migrations
- Build frontend assets

## Configuration

### Security Configuration

Review and configure security settings in `.env`:

```env
# Session Configuration
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false  # Set to true in production with HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Password Hashing
BCRYPT_ROUNDS=12
```

See [SECURITY.md](SECURITY.md) for detailed security configuration.

### Cache Configuration

Configure cache settings for optimal performance:

```env
CACHE_DRIVER=redis  # or file, database
QUEUE_CONNECTION=database  # or redis, sync

# Dashboard Cache TTL (seconds)
DASHBOARD_CACHE_TTL_REALTIME=300
DASHBOARD_CACHE_TTL_HISTORICAL=900
DASHBOARD_CACHE_TTL_STATIC=1800
```

### Mail Configuration

Configure email settings for notifications:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@sweep.local
MAIL_FROM_NAME="${APP_NAME}"
```

## Usage

### Default Test Accounts

After running database seeders (`php artisan db:seed`), you can log in with these test accounts:

#### Administrator Account
- **Email**: `admin@sweep.local`
- **Password**: `123123123`
- **Access**: Full system administration, user management, analytics, and reporting

#### Collection Crew Account
- **Email**: `john.smith@sweep.local`
- **Password**: `123123123`
- **Access**: View assigned routes, log collections, upload photos, track performance

#### Resident Account
- **Email**: `john.doe@example.com`
- **Password**: `password`
- **Access**: View collection schedules, submit reports, track issue status

> **Security Warning**: These are test credentials for development only. Change all passwords immediately in production environments!

### Creating Users

Administrators can create users through the web interface:

1. Log in as administrator
2. Navigate to Users → Create New User
3. Fill in user details and assign role
4. User receives email with login credentials

### Managing Routes and Schedules

1. Navigate to Routes → Create New Route
2. Define route name, zone, and waypoints
3. Create schedule and assign collection days
4. Assign trucks and crew to routes

### Logging Collections

Collection crew can log completed collections:

1. View assigned routes on crew dashboard
2. Mark collection points as completed
3. Upload photos as proof of collection
4. Add notes for any issues encountered

### Resident Reporting

Residents can submit reports:

1. Navigate to Reports → Submit New Report
2. Select issue type and location
3. Upload photos (optional)
4. Track report status in real-time

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Suites

- **Unit Tests**: Service layer, helpers, and utilities
- **Feature Tests**: HTTP requests, authentication, and workflows
- **Accessibility Tests**: WCAG 2.1 AA compliance

## Performance Optimization

### Database Indexes

The system includes optimized indexes for:
- Collection logs by date and status
- Assignments by date, crew, and route
- Reports by status and creation date
- Recycling logs by date range

See [docs/PERFORMANCE_OPTIMIZATION.md](docs/PERFORMANCE_OPTIMIZATION.md) for details.

### Caching Strategy

- **Realtime metrics**: 5-minute cache
- **Historical trends**: 15-minute cache
- **Static comparisons**: 30-minute cache
- **Automatic invalidation**: On data changes

### Queue Workers

For production, run queue workers to process background jobs:

```bash
# Start queue worker
php artisan queue:work

# With supervisor (recommended)
php artisan queue:work --daemon --tries=3
```

### Scheduled Tasks

Add to crontab for scheduled reports and cleanup:

```bash
* * * * * cd /path-to-sweep && php artisan schedule:run >> /dev/null 2>&1
```

## Accessibility

SWEEP is built with accessibility in mind, meeting WCAG 2.1 AA standards:

- Keyboard navigation support
- Screen reader compatibility
- High contrast mode support
- ARIA labels and landmarks
- Responsive touch targets
- Text alternatives for charts

See [docs/ACCESSIBILITY.md](docs/ACCESSIBILITY.md) for complete documentation.

## API Documentation

### Dashboard Metrics API

```php
// Get admin dashboard metrics
GET /api/dashboard/admin?period=30days&route_id=1

// Get crew performance metrics
GET /api/dashboard/crew/{userId}?start_date=2024-01-01&end_date=2024-01-31

// Get resident dashboard
GET /api/dashboard/resident
```

### Export API

```php
// Export collection logs
POST /api/exports/collections
{
    "format": "excel",
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "route_id": 1
}

// Export reports
POST /api/exports/reports
{
    "format": "pdf",
    "status": "pending"
}
```

## Maintenance

### Cleanup Commands

```bash
# Clean up old performance logs (older than 30 days)
php artisan dashboard:cleanup-logs --days=30

# Clean up old export files (older than 7 days)
php artisan dashboard:cleanup-exports --days=7

# Process scheduled reports
php artisan reports:process-scheduled
```

### Database Maintenance

```bash
# Optimize database tables
php artisan db:optimize

# Clear expired sessions
php artisan session:gc

# Clear old cache entries
php artisan cache:prune-stale-tags
```

## Troubleshooting

### Common Issues

**Dashboard loads slowly**
- Check logs for slow queries: `storage/logs/laravel.log`
- Review database indexes
- Increase cache TTL
- Consider narrowing default date ranges

**Queue jobs not processing**
- Ensure queue worker is running: `php artisan queue:work`
- Check failed jobs: `php artisan queue:failed`
- Retry failed jobs: `php artisan queue:retry all`

**Images not displaying**
- Verify storage link: `php artisan storage:link`
- Check file permissions: `chmod -R 775 storage`
- Verify `APP_URL` in `.env`

**Cache not clearing**
- Clear all caches: `php artisan optimize:clear`
- Clear specific cache: `php artisan cache:forget key`
- Restart queue workers after code changes

### Debug Mode

Enable debug mode in development:

```env
APP_DEBUG=true
APP_ENV=local
```

> **Warning**: Never enable debug mode in production!

## Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper `APP_URL`
- [ ] Enable `SESSION_SECURE_COOKIE=true`
- [ ] Set strong `APP_KEY`
- [ ] Configure production database
- [ ] Set up SSL/TLS certificate
- [ ] Configure mail server
- [ ] Set up queue workers with supervisor
- [ ] Configure cron for scheduled tasks
- [ ] Set proper file permissions
- [ ] Enable OPcache
- [ ] Configure backup strategy
- [ ] Set up monitoring and alerts

### Optimization Commands

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

See `.env.production.example` for production environment template.

## Documentation

- [System Architecture](sysarch.md) - Detailed system design and architecture
- [Security Configuration](SECURITY.md) - Security best practices and configuration
- [Accessibility Guide](docs/ACCESSIBILITY.md) - WCAG compliance and accessibility features
- [Performance Optimization](docs/PERFORMANCE_OPTIMIZATION.md) - Database and caching strategies
- [Error Handling](docs/DASHBOARD_ERROR_HANDLING.md) - Error handling and logging
- [Quick Reference](docs/QUICK_REFERENCE_ERROR_HANDLING.md) - Quick error handling guide
- [Chart Implementation](CHART_IMPLEMENTATION_SUMMARY.md) - Chart.js integration details

## Project Structure

```
sweep/
├── app/
│   ├── Console/         # Artisan commands
│   ├── Helpers/         # Helper functions
│   ├── Http/
│   │   ├── Controllers/ # Request handlers
│   │   └── Middleware/  # HTTP middleware
│   ├── Jobs/            # Queue jobs
│   ├── Models/          # Eloquent models
│   ├── Observers/       # Model observers
│   ├── Policies/        # Authorization policies
│   ├── Providers/       # Service providers
│   ├── Services/        # Business logic
│   ├── Traits/          # Reusable traits
│   └── View/            # View composers
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── docs/                # Documentation
├── public/              # Public assets
│   ├── css/            # Compiled CSS
│   └── js/             # Compiled JavaScript
├── resources/
│   ├── css/            # Source CSS
│   ├── js/             # Source JavaScript
│   └── views/          # Blade templates
├── routes/              # Route definitions
├── storage/             # Application storage
├── tests/               # Automated tests
└── vendor/              # Composer dependencies
```

## Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed
- Run `php artisan pint` before committing

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:

- Check the [documentation](docs/)
- Review [troubleshooting](#troubleshooting) section
- Open an issue on GitHub
- Contact the development team

## Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI components from [Tailwind CSS](https://tailwindcss.com)
- Charts powered by [Chart.js](https://www.chartjs.org)
- Icons from [Heroicons](https://heroicons.com)
- Permission management by [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

---

**SWEEP** - Making waste management efficient, transparent, and sustainable.
