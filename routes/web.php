<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CrewAssignmentController;
use App\Http\Controllers\CrewScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentScheduleController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TruckAvailabilityController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle user authentication including login, logout,
| password reset, and email verification. Public authentication routes
| (login, password reset) are defined in routes/auth.php.
| The RateLimitLogin middleware is applied to the login POST route.
|
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
|
| These routes require authentication and are accessible to all
| authenticated users regardless of role.
|
*/

// Main dashboard route - redirects to role-specific dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile management routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Role-Specific Dashboard Routes
|--------------------------------------------------------------------------
|
| These routes provide role-specific dashboard views. Each role has
| access to their own dashboard, and administrators can access all.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->middleware('role:administrator')
        ->name('admin.dashboard');
    
    Route::post('/admin/dashboard/dismiss-alert', [DashboardController::class, 'dismissAlert'])
        ->middleware('role:administrator')
        ->name('admin.dashboard.dismiss-alert');
    
    Route::get('/crew/dashboard', [DashboardController::class, 'crewDashboard'])
        ->middleware('role:collection_crew,administrator')
        ->name('crew.dashboard');
    
    Route::get('/resident/dashboard', [DashboardController::class, 'residentDashboard'])
        ->middleware('role:resident,administrator')
        ->name('resident.dashboard');
});

/*
|--------------------------------------------------------------------------
| Administrator Routes
|--------------------------------------------------------------------------
|
| These routes are only accessible to users with the administrator role.
| They handle user management including creation, editing, deletion,
| and role assignment.
|
*/

Route::middleware(['auth', 'role:administrator'])->prefix('admin')->name('admin.')->group(function () {
    // User management resource routes
    Route::resource('users', UserManagementController::class);
    
    // Additional user management routes
    Route::patch('users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    
    // Route management resource routes
    Route::resource('routes', RouteController::class);
    
    // Schedule management resource routes
    Route::resource('schedules', ScheduleController::class);
    
    // Additional schedule management routes
    Route::get('schedules/{schedule}/duplicate', [ScheduleController::class, 'duplicate'])->name('schedules.duplicate');
    Route::post('schedules/{schedule}/duplicate', [ScheduleController::class, 'storeDuplicate'])->name('schedules.store-duplicate');
    Route::patch('schedules/{schedule}/toggle', [ScheduleController::class, 'toggleActive'])->name('schedules.toggle');
    
    // Holiday management resource routes
    Route::resource('holidays', HolidayController::class);
    
    // Truck management resource routes
    Route::resource('trucks', TruckController::class);
    
    // Additional truck management routes
    Route::patch('trucks/{truck}/status', [TruckController::class, 'updateStatus'])->name('trucks.update-status');
    Route::get('trucks/{truck}/history', [TruckController::class, 'history'])->name('trucks.history');
    
    // Assignment management resource routes
    Route::resource('assignments', AssignmentController::class);
    
    // Additional assignment management routes
    Route::get('assignments-calendar/data', [AssignmentController::class, 'getCalendarData'])->name('assignments.calendar.data');
    Route::patch('assignments/{assignment}/cancel', [AssignmentController::class, 'cancel'])->name('assignments.cancel');
    Route::get('assignments-copy', [AssignmentController::class, 'copyForm'])->name('assignments.copy-form');
    Route::post('assignments-copy', [AssignmentController::class, 'copy'])->name('assignments.copy');
    Route::get('unassigned-routes', [AssignmentController::class, 'unassignedRoutes'])->name('assignments.unassigned-routes');
    
    // Truck availability routes
    Route::get('truck-availability', [TruckAvailabilityController::class, 'index'])->name('truck-availability.index');
    Route::get('truck-availability/data', [TruckAvailabilityController::class, 'getAvailability'])->name('truck-availability.data');
});

/*
|--------------------------------------------------------------------------
| Resident Routes
|--------------------------------------------------------------------------
|
| These routes are only accessible to users with the resident role.
| They handle schedule viewing and zone search functionality.
|
*/

Route::middleware(['auth', 'role:resident'])->prefix('resident')->name('resident.')->group(function () {
    // Schedule viewing routes
    Route::get('schedules', [ResidentScheduleController::class, 'index'])->name('schedules');
    Route::get('schedules/search', [ResidentScheduleController::class, 'search'])->name('schedules.search');
    Route::get('schedules/calendar', [ResidentScheduleController::class, 'calendar'])->name('schedules.calendar');
    Route::get('schedules/calendar/data', [ResidentScheduleController::class, 'getCalendarData'])->name('schedules.calendar.data');
});

/*
|--------------------------------------------------------------------------
| Collection Crew Routes
|--------------------------------------------------------------------------
|
| These routes are only accessible to users with the collection_crew role.
| They handle viewing assigned routes and schedules for waste collection.
|
*/

Route::middleware(['auth', 'role:collection_crew,administrator'])->prefix('crew')->name('crew.')->group(function () {
    // Schedule viewing routes
    Route::get('schedules', [CrewScheduleController::class, 'index'])->name('schedules');
    Route::get('schedules/upcoming', [CrewScheduleController::class, 'upcoming'])->name('schedules.upcoming');
    Route::get('routes/{route}', [CrewScheduleController::class, 'show'])->name('routes.show');
    
    // Assignment viewing routes
    Route::get('assignments', [CrewAssignmentController::class, 'index'])->name('assignments');
    Route::get('assignments/upcoming', [CrewAssignmentController::class, 'upcoming'])->name('assignments.upcoming');
});
