# SWEEP Security Configuration

This document outlines the security configurations implemented in the SWEEP application.

## Session Security

### Session Lifetime
- **Configuration**: 120 minutes (2 hours)
- **Location**: `config/session.php` and `SESSION_LIFETIME` in `.env`
- **Purpose**: Sessions automatically expire after 2 hours of inactivity to minimize the risk of unauthorized access from unattended sessions.

### HTTP-Only Cookies
- **Configuration**: Enabled (`SESSION_HTTP_ONLY=true`)
- **Location**: `config/session.php` and `SESSION_HTTP_ONLY` in `.env`
- **Purpose**: Prevents JavaScript from accessing session cookies, providing protection against XSS (Cross-Site Scripting) attacks.

### Secure Cookies (Production)
- **Configuration**: 
  - Development: `SESSION_SECURE_COOKIE=false`
  - Production: `SESSION_SECURE_COOKIE=true` (see `.env.production.example`)
- **Location**: `config/session.php` and `SESSION_SECURE_COOKIE` in `.env`
- **Purpose**: In production, cookies are only transmitted over HTTPS connections, preventing man-in-the-middle attacks.

### SameSite Cookie Attribute
- **Configuration**: `lax`
- **Location**: `config/session.php` and `SESSION_SAME_SITE` in `.env`
- **Purpose**: Provides CSRF protection by controlling when cookies are sent with cross-site requests.

## CSRF Protection

### Token Verification
- **Configuration**: Automatic via Laravel's web middleware
- **Location**: Applied automatically to all web routes
- **Purpose**: All POST, PUT, PATCH, and DELETE requests require a valid CSRF token, preventing cross-site request forgery attacks.

### Token Refresh
- **Configuration**: Automatic on each page load
- **Purpose**: CSRF tokens are automatically regenerated and included in forms and AJAX requests through Laravel's `@csrf` directive and meta tags.

## Password Security

### Hashing Algorithm
- **Configuration**: Bcrypt with cost factor 12
- **Location**: `config/hashing.php` and `BCRYPT_ROUNDS` in `.env`
- **Purpose**: Uses industry-standard bcrypt hashing with a cost factor of 12, providing strong protection against brute-force attacks while maintaining reasonable performance.

### Password Requirements
- **Minimum Length**: 8 characters
- **Validation**: Enforced in user creation and password change forms
- **Location**: Form request validation in controllers

### Password Rehashing
- **Configuration**: Enabled (`rehash_on_login: true`)
- **Location**: `config/hashing.php`
- **Purpose**: Automatically rehashes passwords on login if the cost factor has been increased, allowing graceful security upgrades.

## Authentication Security

### Rate Limiting
- **Configuration**: 5 attempts per 15 minutes, 30-minute lockout
- **Location**: `app/Http/Middleware/RateLimitLogin.php`
- **Purpose**: Prevents brute-force attacks on login endpoints.

### Session Driver
- **Configuration**: Database
- **Location**: `SESSION_DRIVER=database` in `.env`
- **Purpose**: Stores sessions in the database for better security and scalability compared to file-based sessions.

## Production Deployment Checklist

When deploying to production, ensure the following environment variables are properly configured:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Enable secure cookies for HTTPS
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Maintain 2-hour session timeout
SESSION_LIFETIME=120

# Use strong password hashing
BCRYPT_ROUNDS=12
```

## Additional Security Measures

1. **Role-Based Access Control**: Implemented via Spatie Laravel Permission
2. **Soft Deletes**: User accounts are soft-deleted to maintain audit trails
3. **Role Change Auditing**: All role changes are logged with timestamps
4. **Last Admin Protection**: System prevents deletion or role change of the last administrator

## Security Updates

This configuration follows Laravel 11 security best practices and should be reviewed periodically for updates and improvements.
