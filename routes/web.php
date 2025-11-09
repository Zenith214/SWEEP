<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CrewAssignmentController;
use App\Http\Controllers\CrewScheduleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecyclingLogController;
use App\Http\Controllers\ReportAnalyticsController;
use App\Http\Controllers\ResidentReportController;
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
    
    // Collection log management routes
    Route::get('collection-logs', [\App\Http\Controllers\AdminCollectionLogController::class, 'index'])->name('collection-logs.index');
    Route::get('collection-logs/issues/analysis', [\App\Http\Controllers\AdminCollectionLogController::class, 'issueAnalysis'])->name('collection-logs.issues.analysis');
    Route::get('collection-logs/{collectionLog}', [\App\Http\Controllers\AdminCollectionLogController::class, 'show'])->name('collection-logs.show');
    Route::post('collection-logs/{collectionLog}/notes', [\App\Http\Controllers\AdminCollectionLogController::class, 'addNote'])->name('collection-logs.notes.add');
    Route::get('routes/{route}/issues', [\App\Http\Controllers\AdminCollectionLogController::class, 'routeIssues'])->name('routes.issues');
    
    // Collection analytics routes
    Route::get('analytics/collections', [\App\Http\Controllers\CollectionAnalyticsController::class, 'index'])->name('analytics.collections.index');
    Route::get('analytics/collections/completion-rates', [\App\Http\Controllers\CollectionAnalyticsController::class, 'getCompletionRates'])->name('analytics.collections.completion-rates');
    Route::get('analytics/collections/status-breakdown', [\App\Http\Controllers\CollectionAnalyticsController::class, 'getStatusBreakdown'])->name('analytics.collections.status-breakdown');
    Route::get('analytics/collections/crew-performance', [\App\Http\Controllers\CollectionAnalyticsController::class, 'getCrewPerformance'])->name('analytics.collections.crew-performance');
    Route::get('analytics/collections/route-performance', [\App\Http\Controllers\CollectionAnalyticsController::class, 'getRoutePerformance'])->name('analytics.collections.route-performance');
    
    // Report management routes
    Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');
    Route::patch('reports/{report}/status', [AdminReportController::class, 'updateStatus'])->name('reports.update-status');
    Route::post('reports/{report}/responses', [AdminReportController::class, 'addResponse'])->name('reports.add-response');
    Route::patch('reports/{report}/assign', [AdminReportController::class, 'assign'])->name('reports.assign');
    Route::patch('reports/{report}/unassign', [AdminReportController::class, 'unassign'])->name('reports.unassign');
    
    // Report analytics routes
    Route::get('analytics/reports', [ReportAnalyticsController::class, 'index'])->name('analytics.reports.index');
    Route::get('analytics/reports/location', [ReportAnalyticsController::class, 'locationAnalysis'])->name('analytics.reports.location');
    Route::get('analytics/reports/type', [ReportAnalyticsController::class, 'typeAnalysis'])->name('analytics.reports.type');
    Route::get('analytics/reports/type-distribution', [ReportAnalyticsController::class, 'getTypeDistribution'])->name('analytics.reports.type-distribution');
    Route::get('analytics/reports/resolution-times', [ReportAnalyticsController::class, 'getResolutionTimes'])->name('analytics.reports.resolution-times');
    Route::get('analytics/reports/status-trend', [ReportAnalyticsController::class, 'getStatusTrend'])->name('analytics.reports.status-trend');
    
    // Recycling log management routes
    Route::get('recycling-logs', [\App\Http\Controllers\Admin\RecyclingLogController::class, 'index'])->name('recycling-logs.index');
    Route::get('recycling-logs/{recyclingLog}', [\App\Http\Controllers\Admin\RecyclingLogController::class, 'show'])->name('recycling-logs.show');
    Route::get('recycling-logs-export', [\App\Http\Controllers\Admin\RecyclingLogController::class, 'export'])->name('recycling-logs.export');
    
    // Recycling analytics routes
    Route::get('recycling/analytics', [\App\Http\Controllers\Admin\RecyclingAnalyticsController::class, 'dashboard'])->name('recycling.analytics.dashboard');
    Route::get('recycling/analytics/materials', [\App\Http\Controllers\Admin\RecyclingAnalyticsController::class, 'materialAnalysis'])->name('recycling.analytics.materials');
    Route::get('recycling/analytics/zones', [\App\Http\Controllers\Admin\RecyclingAnalyticsController::class, 'zonePerformance'])->name('recycling.analytics.zones');
    Route::get('recycling/analytics/trends', [\App\Http\Controllers\Admin\RecyclingAnalyticsController::class, 'trendAnalysis'])->name('recycling.analytics.trends');
    Route::get('recycling/analytics/crew', [\App\Http\Controllers\Admin\RecyclingAnalyticsController::class, 'crewPerformance'])->name('recycling.analytics.crew');
    
    // Recycling target management routes
    Route::get('recycling/targets', [\App\Http\Controllers\Admin\RecyclingTargetController::class, 'index'])->name('recycling.targets.index');
    Route::post('recycling/targets', [\App\Http\Controllers\Admin\RecyclingTargetController::class, 'store'])->name('recycling.targets.store');
    Route::put('recycling/targets/{recyclingTarget}', [\App\Http\Controllers\Admin\RecyclingTargetController::class, 'update'])->name('recycling.targets.update');
    Route::delete('recycling/targets/{recyclingTarget}', [\App\Http\Controllers\Admin\RecyclingTargetController::class, 'destroy'])->name('recycling.targets.destroy');
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
    
    // Report submission and tracking routes
    Route::get('reports', [ResidentReportController::class, 'index'])->name('reports');
    Route::get('reports/create', [ResidentReportController::class, 'create'])->name('reports.create');
    Route::post('reports', [ResidentReportController::class, 'store'])->name('reports.store');
    Route::get('reports/search', [ResidentReportController::class, 'search'])->name('reports.search');
    Route::get('reports/{report}', [ResidentReportController::class, 'show'])->name('reports.show');
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
    
    // Collection logging routes
    Route::get('collections', [\App\Http\Controllers\CollectionLogController::class, 'index'])->name('collections');
    Route::get('collections/history', [\App\Http\Controllers\CollectionLogController::class, 'history'])->name('collections.history');
    Route::get('assignments/{assignment}/log', [\App\Http\Controllers\CollectionLogController::class, 'create'])->name('collections.create');
    Route::post('assignments/{assignment}/log', [\App\Http\Controllers\CollectionLogController::class, 'store'])->name('collections.store');
    Route::get('collections/{collectionLog}', [\App\Http\Controllers\CollectionLogController::class, 'show'])->name('collections.show');
    Route::get('collections/{collectionLog}/edit', [\App\Http\Controllers\CollectionLogController::class, 'edit'])
        ->middleware('ensure.log.editable')
        ->name('collections.edit');
    Route::patch('collections/{collectionLog}', [\App\Http\Controllers\CollectionLogController::class, 'update'])
        ->middleware('ensure.log.editable')
        ->name('collections.update');
    Route::post('collections/{collectionLog}/photos', [\App\Http\Controllers\CollectionLogController::class, 'uploadPhoto'])
        ->middleware('ensure.log.editable')
        ->name('collections.photos.upload');
    Route::delete('photos/{photo}', [\App\Http\Controllers\CollectionLogController::class, 'deletePhoto'])
        ->name('collections.photos.delete');
    
    // Recycling log routes
    Route::get('recycling-logs', [\App\Http\Controllers\RecyclingLogController::class, 'index'])->name('recycling-logs.index');
    Route::get('recycling-logs/create', [\App\Http\Controllers\RecyclingLogController::class, 'create'])->name('recycling-logs.create');
    Route::post('recycling-logs', [\App\Http\Controllers\RecyclingLogController::class, 'store'])->name('recycling-logs.store');
    Route::get('recycling-logs/{recyclingLog}/edit', [\App\Http\Controllers\RecyclingLogController::class, 'edit'])->name('recycling-logs.edit');
    Route::put('recycling-logs/{recyclingLog}', [\App\Http\Controllers\RecyclingLogController::class, 'update'])->name('recycling-logs.update');
});
