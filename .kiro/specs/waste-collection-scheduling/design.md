# Design Document: Waste Collection Scheduling

## Overview

This design implements a comprehensive route and schedule management system for SWEEP that allows administrators to create collection routes, define recurring schedules with holiday exceptions, and provides intuitive schedule viewing for residents and crew members. The system uses Laravel's Eloquent ORM with a relational database structure to manage routes, schedules, and holiday exceptions.

## Architecture

### Technology Stack
- **Framework**: Laravel 11.x
- **Database**: MariaDB
- **Frontend**: Blade templates with Bootstrap 5
- **Calendar UI**: FullCalendar.js or custom calendar component
- **Authorization**: Spatie Laravel Permission (from User Management feature)

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  - Route Management Views (Admin)                           │
│  - Schedule Management Views (Admin)                         │
│  - Schedule Calendar (Resident)                              │
│  - Route Assignment View (Crew)                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  - RouteController                                           │
│  - ScheduleController                                        │
│  - HolidayController                                         │
│  - ResidentScheduleController                                │
│  - CrewScheduleController                                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  - Route Model                                               │
│  - Schedule Model                                            │
│  - Holiday Model                                             │
│  - ScheduleService (business logic)                          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  - routes table                                              │
│  - schedules table                                           │
│  - holidays table                                            │
│  - schedule_days table (pivot)                               │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### Routes Table
```sql
routes
- id: bigint (PK)
- name: varchar(255) UNIQUE
- zone: varchar(100)
- description: text NULL
- notes: text NULL
- is_active: boolean DEFAULT true
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp NULL (soft deletes)
```

#### Schedules Table
```sql
schedules
- id: bigint (PK)
- route_id: bigint (FK to routes)
- collection_time: time
- start_date: date
- end_date: date NULL
- is_active: boolean DEFAULT true
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp NULL (soft deletes)
```

#### Schedule Days Table (Pivot)
```sql
schedule_days
- id: bigint (PK)
- schedule_id: bigint (FK to schedules)
- day_of_week: tinyint (0=Sunday, 1=Monday, ..., 6=Saturday)
- created_at: timestamp
- updated_at: timestamp

UNIQUE KEY (schedule_id, day_of_week)
```

#### Holidays Table
```sql
holidays
- id: bigint (PK)
- name: varchar(255)
- date: date
- is_collection_skipped: boolean DEFAULT true
- reschedule_date: date NULL
- created_at: timestamp
- updated_at: timestamp

UNIQUE KEY (date)
```

### 2. Models

#### Route Model
```php
class Route extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'zone',
        'description',
        'notes',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    // Relationships
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    
    public function activeSchedules()
    {
        return $this->hasMany(Schedule::class)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            });
    }
    
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    // Helper methods
    public function hasActiveSchedules(): bool
    public function getNextCollectionDate(): ?Carbon
}
```

#### Schedule Model
```php
class Schedule extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'route_id',
        'collection_time',
        'start_date',
        'end_date',
        'is_active'
    ];
    
    protected $casts = [
        'collection_time' => 'datetime:H:i',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];
    
    // Relationships
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
    
    public function scheduleDays()
    {
        return $this->hasMany(ScheduleDay::class);
    }
    
    public function getDaysOfWeek()
    {
        return $this->scheduleDays->pluck('day_of_week')->toArray();
    }
    
    // Helper methods
    public function isActiveOn(Carbon $date): bool
    public function getCollectionDatesInRange(Carbon $start, Carbon $end): Collection
    public function hasConflictWith(Schedule $other): bool
}
```

#### ScheduleDay Model
```php
class ScheduleDay extends Model
{
    protected $fillable = ['schedule_id', 'day_of_week'];
    
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
    
    // Constants for day mapping
    public const DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
    ];
}
```

#### Holiday Model
```php
class Holiday extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'date',
        'is_collection_skipped',
        'reschedule_date'
    ];
    
    protected $casts = [
        'date' => 'date',
        'reschedule_date' => 'date',
        'is_collection_skipped' => 'boolean'
    ];
    
    // Helper methods
    public static function isHoliday(Carbon $date): bool
    public static function getRescheduledDate(Carbon $date): ?Carbon
    public static function getHolidaysInRange(Carbon $start, Carbon $end): Collection
}
```

### 3. Controllers

#### RouteController (Admin)
```php
class RouteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all routes with schedule status and search/filter
    
    public function create()
    // Show route creation form
    
    public function store(StoreRouteRequest $request)
    // Create new route with validation
    
    public function show(Route $route)
    // Display route details with associated schedules
    
    public function edit(Route $route)
    // Show route edit form
    
    public function update(UpdateRouteRequest $request, Route $route)
    // Update route information
    
    public function destroy(Route $route)
    // Soft delete route (only if no active schedules)
}
```

#### ScheduleController (Admin)
```php
class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all schedules with route information
    
    public function create()
    // Show schedule creation form with route selection
    
    public function store(StoreScheduleRequest $request)
    // Create new schedule with days validation
    
    public function show(Schedule $schedule)
    // Display schedule details
    
    public function edit(Schedule $schedule)
    // Show schedule edit form
    
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    // Update schedule with conflict checking
    
    public function destroy(Schedule $schedule)
    // Soft delete schedule
    
    public function duplicate(Schedule $schedule)
    // Show duplication form
    
    public function storeDuplicate(DuplicateScheduleRequest $request, Schedule $schedule)
    // Create duplicate schedule for different route
    
    public function toggleActive(Schedule $schedule)
    // Activate/deactivate schedule
}
```

#### HolidayController (Admin)
```php
class HolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all holidays
    
    public function create()
    // Show holiday creation form
    
    public function store(StoreHolidayRequest $request)
    // Create new holiday
    
    public function edit(Holiday $holiday)
    // Show holiday edit form
    
    public function update(UpdateHolidayRequest $request, Holiday $holiday)
    // Update holiday information
    
    public function destroy(Holiday $holiday)
    // Delete holiday
}
```

#### ResidentScheduleController
```php
class ResidentScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:resident']);
    }
    
    public function index()
    // Show zone search interface
    
    public function search(Request $request)
    // Search schedules by zone
    
    public function calendar(Request $request)
    // Display calendar view for selected zone
    
    public function getCalendarData(Request $request)
    // API endpoint for calendar events (AJAX)
}
```

#### CrewScheduleController
```php
class CrewScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:collection_crew']);
    }
    
    public function index()
    // Display assigned routes for current day
    
    public function upcoming()
    // Display upcoming routes for next 7 days
    
    public function show(Route $route)
    // Display route details with schedule
}
```

### 4. Services

#### ScheduleService
```php
class ScheduleService
{
    public function createSchedule(array $data): Schedule
    // Create schedule with days and validation
    
    public function updateSchedule(Schedule $schedule, array $data): Schedule
    // Update schedule with conflict checking
    
    public function duplicateSchedule(Schedule $schedule, Route $targetRoute): Schedule
    // Duplicate schedule to another route
    
    public function checkConflicts(Schedule $schedule, ?Schedule $exclude = null): bool
    // Check for scheduling conflicts on same route
    
    public function getCollectionDatesForZone(string $zone, Carbon $start, Carbon $end): Collection
    // Get all collection dates for a zone in date range
    
    public function getNextCollectionForRoute(Route $route): ?array
    // Get next collection date and time for a route
    
    public function applyHolidayExceptions(Collection $dates): Collection
    // Filter out holidays and apply rescheduled dates
    
    public function getRoutesWithoutSchedules(): Collection
    // Get routes that have no active schedules
    
    public function getScheduleCoverage(): array
    // Calculate percentage of routes with active schedules
}
```

### 5. Form Requests

#### StoreRouteRequest
```php
class StoreRouteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:routes,name',
            'zone' => 'required|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }
}
```

#### UpdateRouteRequest
```php
class UpdateRouteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:routes,name,' . $this->route->id,
            'zone' => 'required|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ];
    }
}
```

#### StoreScheduleRequest
```php
class StoreScheduleRequest extends FormRequest
{
    public function rules()
    {
        return [
            'route_id' => 'required|exists:routes,id',
            'collection_time' => 'required|date_format:H:i',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'integer|between:0,6',
            'is_active' => 'boolean'
        ];
    }
}
```

### 6. Routes

```php
// Administrator routes for route management
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::resource('routes', RouteController::class);
    Route::resource('schedules', ScheduleController::class);
    Route::post('schedules/{schedule}/duplicate', [ScheduleController::class, 'storeDuplicate']);
    Route::patch('schedules/{schedule}/toggle', [ScheduleController::class, 'toggleActive']);
    Route::resource('holidays', HolidayController::class);
});

// Resident routes for schedule viewing
Route::middleware(['auth', 'role:resident'])->prefix('resident')->group(function () {
    Route::get('schedules', [ResidentScheduleController::class, 'index'])->name('resident.schedules');
    Route::get('schedules/search', [ResidentScheduleController::class, 'search']);
    Route::get('schedules/calendar', [ResidentScheduleController::class, 'calendar']);
    Route::get('schedules/calendar/data', [ResidentScheduleController::class, 'getCalendarData']);
});

// Crew routes for assigned schedules
Route::middleware(['auth', 'role:collection_crew'])->prefix('crew')->group(function () {
    Route::get('schedules', [CrewScheduleController::class, 'index'])->name('crew.schedules');
    Route::get('schedules/upcoming', [CrewScheduleController::class, 'upcoming']);
    Route::get('routes/{route}', [CrewScheduleController::class, 'show']);
});
```

## Data Models

### Route Status
- **Active**: Route is currently in use
- **Inactive**: Route is disabled but not deleted

### Schedule Status
- **Active**: Schedule is currently in effect
- **Inactive**: Schedule is disabled but not deleted

### Day of Week Mapping
```php
0 => Sunday
1 => Monday
2 => Tuesday
3 => Wednesday
4 => Thursday
5 => Friday
6 => Saturday
```

### Holiday Types
- **Skipped**: Collection does not occur on this date
- **Rescheduled**: Collection moved to alternate date

## Error Handling

### Route Management Errors
- **Duplicate Route Name**: "A route with this name already exists"
- **Route Deletion with Active Schedules**: "Cannot delete route with active schedules. Please deactivate or delete schedules first"
- **Invalid Zone Format**: "Please provide a valid zone identifier"

### Schedule Management Errors
- **Schedule Conflict**: "This schedule conflicts with an existing schedule on the same route"
- **Invalid Date Range**: "End date must be after start date"
- **No Days Selected**: "Please select at least one collection day"
- **Past Start Date**: "Start date cannot be in the past"
- **Duplication Conflict**: "Cannot duplicate schedule - would create conflict on target route"

### Holiday Errors
- **Duplicate Holiday Date**: "A holiday already exists for this date"
- **Invalid Reschedule Date**: "Reschedule date must be different from holiday date"

### Resident View Errors
- **Zone Not Found**: "No collection schedules found for this zone"
- **Invalid Zone**: "Please enter a valid zone identifier"

## Testing Strategy

### Unit Tests
- Route model methods (hasActiveSchedules, getNextCollectionDate)
- Schedule model methods (isActiveOn, getCollectionDatesInRange, hasConflictWith)
- Holiday model methods (isHoliday, getRescheduledDate)
- ScheduleService business logic methods

### Feature Tests
- Route CRUD operations by administrator
- Schedule CRUD operations with day selection
- Schedule conflict detection
- Schedule duplication functionality
- Holiday management
- Resident zone search and calendar view
- Crew schedule viewing
- Route deletion prevention with active schedules

### Integration Tests
- Complete schedule creation flow with days
- Calendar data generation with holidays
- Next collection date calculation with holidays
- Schedule coverage reporting

## UI/UX Design

### Administrator Views

#### Routes Index
- Data table with columns: Name, Zone, Active Schedules, Status, Actions
- Search bar for name/zone filtering
- Filter dropdown for active/inactive routes
- "Create Route" button (Teal accent)
- Schedule status indicator (badge showing count)
- Warning icon for routes without schedules

#### Route Create/Edit Form
- Name input (required)
- Zone input (required)
- Description textarea
- Notes textarea
- Active checkbox
- Save button (Teal) / Cancel button

#### Schedules Index
- Data table with columns: Route, Zone, Days, Time, Date Range, Status, Actions
- Filter by route dropdown
- Filter by active/inactive
- "Create Schedule" button (Teal accent)
- Days displayed as badges (Mon, Tue, Wed, etc.)

#### Schedule Create/Edit Form
- Route selection dropdown
- Day checkboxes (Sun-Sat) with visual selection
- Time picker for collection time
- Start date picker
- End date picker (optional)
- Active checkbox
- Conflict warning display
- Save button (Teal) / Cancel button

#### Holiday Management
- Calendar view showing defined holidays
- List view with holiday details
- "Add Holiday" button
- Holiday form with name, date, skip/reschedule options
- Reschedule date picker (conditional)

### Resident Views

#### Schedule Search
- Zone input with search button
- Recent searches display
- Instructions for finding zone identifier

#### Schedule Calendar
- Monthly calendar view using FullCalendar.js or custom component
- Collection days highlighted in Forest Green
- Click on date shows route and time details
- Legend showing color coding
- Month navigation arrows
- Holiday indicators in Amber
- Next collection date prominently displayed at top

#### Schedule List View (Alternative)
- List of upcoming collections
- Route name, date, time for each
- Next 30 days displayed
- Holiday notices

### Crew Views

#### Today's Routes
- Card layout showing assigned routes
- Route name and zone prominently displayed
- Collection time
- Special instructions/notes
- "View Details" button for each route
- Map icon (placeholder for future map integration)

#### Upcoming Schedule
- 7-day view of upcoming assignments
- Grouped by date
- Route cards with essential info
- Empty state if no assignments

## Security Considerations

1. **Authorization**
   - Only administrators can create/edit routes and schedules
   - Residents can only view schedules
   - Crew can only view assigned routes

2. **Input Validation**
   - Validate all date inputs
   - Sanitize zone identifiers
   - Validate time format
   - Prevent SQL injection through Eloquent

3. **Data Integrity**
   - Prevent route deletion with active schedules
   - Validate schedule conflicts before saving
   - Ensure at least one day selected for schedules
   - Validate date ranges

## Implementation Notes

1. Use soft deletes for routes and schedules to maintain historical data
2. Implement eager loading to prevent N+1 queries when displaying routes with schedules
3. Cache frequently accessed data like holiday lists
4. Use database transactions when creating schedules with multiple days
5. Consider indexing zone column for faster resident searches
6. Implement AJAX for calendar data loading to improve performance
7. Use Carbon for all date/time calculations
8. Create database seeders for sample routes and schedules for testing
9. Implement schedule conflict checking at both application and database level
10. Consider adding route map visualization in future iterations
