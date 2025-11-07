# Design Document: User Management & Authentication

## Overview

This design implements a secure, role-based authentication system for SWEEP using Laravel Breeze for authentication scaffolding and Spatie Laravel Permission for role and permission management. The system supports three user roles (Administrator, Collection Crew, Resident) with distinct access levels and dashboard experiences.

## Architecture

### Technology Stack
- **Framework**: Laravel 11.x
- **Authentication**: Laravel Breeze (Blade stack)
- **Authorization**: Spatie Laravel Permission
- **Database**: MariaDB
- **Frontend**: Blade templates with Bootstrap 5
- **Session Management**: Laravel's built-in session handling

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  (Blade Templates + Bootstrap 5)                            │
│  - Login/Register Views                                      │
│  - Role-specific Dashboards                                  │
│  - User Management Interface (Admin)                         │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  - Authentication Controllers (Breeze)                       │
│  - User Management Controllers                               │
│  - Middleware (auth, role, permission)                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  - User Model                                                │
│  - Role & Permission Models (Spatie)                         │
│  - Business Logic Services                                   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  - MariaDB Database                                          │
│  - Eloquent ORM                                              │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### Users Table (Extended from Breeze)
```sql
users
- id: bigint (PK)
- name: varchar(255)
- email: varchar(255) UNIQUE
- email_verified_at: timestamp NULL
- password: varchar(255)
- remember_token: varchar(100) NULL
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp NULL (soft deletes)
```

#### Spatie Permission Tables
```sql
roles
- id: bigint (PK)
- name: varchar(255)
- guard_name: varchar(255)
- created_at: timestamp
- updated_at: timestamp

permissions
- id: bigint (PK)
- name: varchar(255)
- guard_name: varchar(255)
- created_at: timestamp
- updated_at: timestamp

model_has_roles
- role_id: bigint (FK)
- model_type: varchar(255)
- model_id: bigint

role_has_permissions
- permission_id: bigint (FK)
- role_id: bigint (FK)
```

#### Role Change Audit Log
```sql
role_change_logs
- id: bigint (PK)
- user_id: bigint (FK to users)
- changed_by: bigint (FK to users)
- old_role: varchar(255)
- new_role: varchar(255)
- created_at: timestamp
```

### 2. Models

#### User Model
```php
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;
    
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];
    
    // Relationships
    public function roleChangeLogs()
    public function getDashboardRoute(): string
}
```

#### RoleChangeLog Model
```php
class RoleChangeLog extends Model
{
    protected $fillable = ['user_id', 'changed_by', 'old_role', 'new_role'];
    
    public function user()
    public function changedBy()
}
```

### 3. Controllers

#### AuthenticatedSessionController (Extended from Breeze)
- `create()`: Display login form
- `store()`: Handle login with rate limiting
- `destroy()`: Handle logout

#### RegisteredUserController (Modified from Breeze)
- Disabled for public registration
- Only accessible by administrators

#### UserManagementController (New)
- `index()`: List all users with filtering
- `create()`: Show user creation form
- `store()`: Create new user
- `edit($id)`: Show user edit form
- `update($id)`: Update user details
- `destroy($id)`: Soft delete user
- `updateRole($id)`: Change user role

#### ProfileController (Extended from Breeze)
- `edit()`: Show profile edit form
- `update()`: Update profile information
- `updatePassword()`: Change password

#### DashboardController (New)
- `index()`: Route to role-specific dashboard
- `adminDashboard()`: Administrator dashboard
- `crewDashboard()`: Collection Crew dashboard
- `residentDashboard()`: Resident dashboard

### 4. Middleware

#### EnsureUserHasRole (Custom)
```php
class EnsureUserHasRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!auth()->user()->hasAnyRole($roles)) {
            abort(403, 'Access denied');
        }
        return $next($request);
    }
}
```

#### RateLimitLogin (Custom)
```php
class RateLimitLogin
{
    // Implements 5 attempts per 15 minutes
    // Blocks for 30 minutes after limit exceeded
}
```

### 5. Services

#### UserService
```php
class UserService
{
    public function createUser(array $data, string $role): User
    public function updateUser(User $user, array $data): User
    public function changeUserRole(User $user, string $newRole, User $admin): void
    public function deleteUser(User $user): bool
    public function ensureAdminExists(): void
}
```

### 6. Routes

```php
// Public routes
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::get('/forgot-password', [PasswordResetController::class, 'create']);
Route::post('/forgot-password', [PasswordResetController::class, 'store']);
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit']);
Route::post('/reset-password', [PasswordResetController::class, 'update']);

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);
});

// Administrator routes
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::resource('users', UserManagementController::class);
    Route::patch('users/{user}/role', [UserManagementController::class, 'updateRole']);
});
```

## Data Models

### User Roles
- **administrator**: Full system access
- **collection_crew**: Access to route and collection features
- **resident**: Access to schedule viewing and reporting features

### Permissions Structure
```
users.create
users.read
users.update
users.delete
users.manage_roles

routes.create
routes.read
routes.update
routes.delete

schedules.create
schedules.read
schedules.update
schedules.delete

collections.create
collections.read
collections.update

reports.create
reports.read
reports.update
reports.delete

recycling.create
recycling.read
recycling.update

dashboard.admin
dashboard.crew
dashboard.resident
```

### Role-Permission Mapping
```php
'administrator' => [
    'users.*',
    'routes.*',
    'schedules.*',
    'collections.*',
    'reports.*',
    'recycling.*',
    'dashboard.admin'
],
'collection_crew' => [
    'routes.read',
    'schedules.read',
    'collections.*',
    'dashboard.crew'
],
'resident' => [
    'schedules.read',
    'reports.create',
    'reports.read',
    'dashboard.resident'
]
```

## Error Handling

### Authentication Errors
- **Invalid Credentials**: Display "These credentials do not match our records" message
- **Rate Limit Exceeded**: Display "Too many login attempts. Please try again in X minutes"
- **Account Deactivated**: Display "Your account has been deactivated. Please contact an administrator"

### Authorization Errors
- **Insufficient Permissions**: Redirect to role-appropriate dashboard with flash message
- **Invalid Role Assignment**: Prevent and display error message
- **Last Admin Protection**: Prevent role change/deletion with error message

### Validation Errors
- **Duplicate Email**: "This email address is already registered"
- **Weak Password**: "Password must be at least 8 characters"
- **Invalid Email Format**: "Please provide a valid email address"
- **Missing Required Fields**: Field-specific error messages

### Session Errors
- **Session Expired**: Redirect to login with "Your session has expired. Please log in again"
- **Concurrent Session**: Allow multiple sessions but track for security

## Testing Strategy

### Unit Tests
- User model methods and relationships
- UserService business logic
- Role and permission assignment
- Password hashing and verification
- Dashboard route determination

### Feature Tests
- User registration by administrator
- Login with valid/invalid credentials
- Rate limiting functionality
- Password reset flow
- Profile update functionality
- Role assignment and changes
- Access control for different roles
- Logout functionality

### Integration Tests
- Complete authentication flow
- Role-based dashboard redirection
- Permission enforcement across controllers
- Audit log creation for role changes

### Security Tests
- SQL injection prevention
- XSS protection in user inputs
- CSRF token validation
- Password strength enforcement
- Session fixation prevention
- Rate limiting effectiveness

## UI/UX Design

### Color Scheme (from sysarch.md)
- Primary: Forest Green (#2E8B57)
- Secondary: Amber (#F4A300)
- Accent: Teal (#4FB4A2)
- Background: Off-white (#F9FAFB)
- Text: Dark Gray (#333333)

### Login Page
- Centered card layout
- SWEEP logo and tagline
- Email and password fields
- "Remember me" checkbox
- "Forgot password?" link
- Login button (Teal accent color)
- Clean, minimal design

### Dashboard Layouts

#### Administrator Dashboard
- Sidebar navigation (Dashboard, Routes, Reports, Recycling, Users, Settings)
- Top bar with user profile dropdown and logout
- Main content area with statistics cards
- Responsive design for mobile/tablet

#### Collection Crew Dashboard
- Simplified navigation (Dashboard, My Routes, Collection Logs)
- Current assignment prominently displayed
- Quick action buttons for logging collections
- Mobile-optimized for field use

#### Resident Dashboard
- Simple navigation (Dashboard, Schedule, My Reports)
- Collection schedule calendar view
- Report submission button
- Report tracking list

### User Management Interface (Admin)
- Data table with search and filter
- Role badges with color coding
- Action buttons (Edit, Delete, Change Role)
- Modal dialogs for confirmations
- Responsive table design

## Security Considerations

1. **Password Security**
   - Bcrypt hashing with cost factor 12
   - Minimum 8 character requirement
   - No password in API responses

2. **Session Security**
   - HTTP-only cookies
   - Secure flag in production
   - 2-hour inactivity timeout
   - CSRF protection on all forms

3. **Rate Limiting**
   - 5 login attempts per 15 minutes
   - 30-minute lockout after limit
   - IP-based tracking

4. **Input Validation**
   - Server-side validation for all inputs
   - Email format validation
   - XSS prevention through Blade escaping
   - SQL injection prevention through Eloquent

5. **Access Control**
   - Middleware-based authorization
   - Permission checks at controller level
   - Prevent privilege escalation
   - Audit logging for sensitive actions

## Implementation Notes

1. Install Laravel Breeze with Blade stack for authentication scaffolding
2. Install Spatie Laravel Permission for role management
3. Customize Breeze views to match SWEEP design system
4. Disable public registration route
5. Create seeder for initial administrator account
6. Create seeder for roles and permissions
7. Implement custom middleware for role-based access
8. Add soft deletes to users table
9. Create role change audit logging
10. Implement rate limiting for login attempts
