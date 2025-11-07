# Design Document: Truck Assignment System

## Overview

This design implements a comprehensive truck fleet management and assignment system for SWEEP that allows administrators to register trucks, track their operational status, and assign them with drivers to specific routes and dates. The system provides visibility into resource allocation, identifies scheduling gaps, and maintains assignment history for operational planning and accountability.

## Architecture

### Technology Stack
- **Framework**: Laravel 11.x
- **Database**: MariaDB
- **Frontend**: Blade templates with Bootstrap 5
- **Calendar UI**: FullCalendar.js for assignment schedule view
- **Authorization**: Spatie Laravel Permission (from User Management feature)
- **Dependencies**: Route and Schedule models from Waste Collection Scheduling feature

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  - Truck Management Views (Admin)                           │
│  - Assignment Management Views (Admin)                       │
│  - Assignment Schedule/Calendar (Admin)                      │
│  - My Assignments View (Crew)                                │
│  - Dashboard Alerts (Admin)                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  - TruckController                                           │
│  - AssignmentController                                      │
│  - CrewAssignmentController                                  │
│  - TruckAvailabilityController                               │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  - Truck Model                                               │
│  - Assignment Model                                          │
│  - TruckStatusHistory Model                                  │
│  - AssignmentService (business logic)                        │
│  - AlertService (dashboard alerts)                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  - trucks table                                              │
│  - assignments table                                         │
│  - truck_status_history table                                │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### Trucks Table
```sql
trucks
- id: bigint (PK)
- truck_number: varchar(50) UNIQUE
- license_plate: varchar(50)
- capacity: decimal(8,2) (in tons or cubic meters)
- operational_status: enum('operational', 'maintenance', 'out_of_service') DEFAULT 'operational'
- notes: text NULL
- created_at: timestamp
- updated_at: timestamp
- deleted_at: timestamp NULL (soft deletes)
```

#### Assignments Table
```sql
assignments
- id: bigint (PK)
- truck_id: bigint (FK to trucks)
- user_id: bigint (FK to users) -- Collection Crew member
- route_id: bigint (FK to routes)
- assignment_date: date
- status: enum('active', 'cancelled') DEFAULT 'active'
- notes: text NULL
- created_at: timestamp
- updated_at: timestamp

UNIQUE KEY (truck_id, assignment_date, status) WHERE status = 'active'
UNIQUE KEY (user_id, assignment_date, status) WHERE status = 'active'
INDEX (assignment_date)
INDEX (truck_id, assignment_date)
INDEX (user_id, assignment_date)
INDEX (route_id, assignment_date)
```

#### Truck Status History Table
```sql
truck_status_history
- id: bigint (PK)
- truck_id: bigint (FK to trucks)
- old_status: enum('operational', 'maintenance', 'out_of_service') NULL
- new_status: enum('operational', 'maintenance', 'out_of_service')
- changed_by: bigint (FK to users)
- notes: text NULL
- created_at: timestamp
```

### 2. Models

#### Truck Model
```php
class Truck extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'truck_number',
        'license_plate',
        'capacity',
        'operational_status',
        'notes'
    ];
    
    protected $casts = [
        'capacity' => 'decimal:2'
    ];
    
    // Constants for operational status
    public const STATUS_OPERATIONAL = 'operational';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_OUT_OF_SERVICE = 'out_of_service';
    
    // Relationships
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    
    public function activeAssignments()
    {
        return $this->hasMany(Assignment::class)
            ->where('status', 'active');
    }
    
    public function statusHistory()
    {
        return $this->hasMany(TruckStatusHistory::class);
    }
    
    // Helper methods
    public function isOperational(): bool
    public function hasAssignmentOn(Carbon $date): bool
    public function getAssignmentOn(Carbon $date): ?Assignment
    public function hasFutureAssignments(): bool
    public function getAssignmentHistory(Carbon $start, Carbon $end): Collection
    public function getUtilizationRate(Carbon $start, Carbon $end): float
}
```

#### Assignment Model
```php
class Assignment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'truck_id',
        'user_id',
        'route_id',
        'assignment_date',
        'status',
        'notes'
    ];
    
    protected $casts = [
        'assignment_date' => 'date'
    ];
    
    // Constants for status
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    
    // Relationships
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    
    public function scopeForDate($query, Carbon $date)
    {
        return $query->where('assignment_date', $date->format('Y-m-d'));
    }
    
    public function scopeUpcoming($query)
    {
        return $query->where('assignment_date', '>=', now()->format('Y-m-d'));
    }
    
    // Helper methods
    public function isActive(): bool
    public function cancel(): void
    public function hasConflictWith(Assignment $other): bool
}
```

#### TruckStatusHistory Model
```php
class TruckStatusHistory extends Model
{
    protected $fillable = [
        'truck_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes'
    ];
    
    public $timestamps = false;
    
    protected $casts = [
        'created_at' => 'datetime'
    ];
    
    // Relationships
    public function truck()
    {
        return $this->belongsTo(Truck::class);
    }
    
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
```

### 3. Controllers

#### TruckController (Admin)
```php
class TruckController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all trucks with status and assignment info
    
    public function create()
    // Show truck registration form
    
    public function store(StoreTruckRequest $request)
    // Create new truck
    
    public function show(Truck $truck)
    // Display truck details with assignment history
    
    public function edit(Truck $truck)
    // Show truck edit form
    
    public function update(UpdateTruckRequest $request, Truck $truck)
    // Update truck information
    
    public function destroy(Truck $truck)
    // Soft delete truck (only if no future assignments)
    
    public function updateStatus(UpdateTruckStatusRequest $request, Truck $truck)
    // Update operational status with history logging
    
    public function history(Truck $truck)
    // Display assignment history for truck
}
```

#### AssignmentController (Admin)
```php
class AssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display assignments in calendar/schedule view
    
    public function create()
    // Show assignment creation form
    
    public function store(StoreAssignmentRequest $request)
    // Create new assignment with validation
    
    public function show(Assignment $assignment)
    // Display assignment details
    
    public function edit(Assignment $assignment)
    // Show assignment edit form
    
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    // Update assignment with conflict checking
    
    public function cancel(Assignment $assignment)
    // Cancel assignment
    
    public function copy(CopyAssignmentsRequest $request)
    // Copy assignments from one date to another
    
    public function getCalendarData(Request $request)
    // API endpoint for calendar events (AJAX)
    
    public function unassignedRoutes(Request $request)
    // Display routes without assignments
}
```

#### TruckAvailabilityController (Admin)
```php
class TruckAvailabilityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index(Request $request)
    // Display truck availability for selected date
    
    public function getAvailability(Request $request)
    // API endpoint for availability data (AJAX)
}
```

#### CrewAssignmentController (Crew)
```php
class CrewAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:collection_crew']);
    }
    
    public function index()
    // Display current day assignment
    
    public function upcoming()
    // Display upcoming assignments for next 14 days
}
```

### 4. Services

#### AssignmentService
```php
class AssignmentService
{
    public function createAssignment(array $data): Assignment
    // Create assignment with validation
    
    public function updateAssignment(Assignment $assignment, array $data): Assignment
    // Update assignment with conflict checking
    
    public function cancelAssignment(Assignment $assignment, ?string $reason = null): void
    // Cancel assignment and log reason
    
    public function checkConflicts(array $data, ?Assignment $exclude = null): array
    // Check for truck and crew conflicts
    
    public function copyAssignments(Carbon $sourceDate, Carbon $targetDate, ?array $filters = null): array
    // Copy assignments with conflict detection
    
    public function getAssignmentsForDate(Carbon $date): Collection
    // Get all active assignments for a date
    
    public function getAssignmentsInRange(Carbon $start, Carbon $end): Collection
    // Get assignments in date range
    
    public function getUnassignedRoutes(Carbon $start, Carbon $end): Collection
    // Get routes with schedules but no assignments
    
    public function getTruckAvailability(Carbon $date): array
    // Get truck availability status for date
    
    public function getCrewAssignments(User $user, Carbon $start, Carbon $end): Collection
    // Get assignments for specific crew member
}
```

#### AlertService
```php
class AlertService
{
    public function getAssignmentAlerts(): array
    // Get all assignment-related alerts for dashboard
    
    public function getUnassignedRoutesAlert(): ?array
    // Get alert for routes without assignments in next 3 days
    
    public function getUnderutilizedTrucksAlert(): ?array
    // Get alert for operational trucks with no assignments in next 7 days
    
    public function dismissAlert(string $alertType, User $user): void
    // Mark alert as dismissed for user
}
```

### 5. Form Requests

#### StoreTruckRequest
```php
class StoreTruckRequest extends FormRequest
{
    public function rules()
    {
        return [
            'truck_number' => 'required|string|max:50|unique:trucks,truck_number',
            'license_plate' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'operational_status' => 'required|in:operational,maintenance,out_of_service',
            'notes' => 'nullable|string'
        ];
    }
}
```

#### UpdateTruckRequest
```php
class UpdateTruckRequest extends FormRequest
{
    public function rules()
    {
        return [
            'truck_number' => 'required|string|max:50|unique:trucks,truck_number,' . $this->truck->id,
            'license_plate' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ];
    }
}
```

#### UpdateTruckStatusRequest
```php
class UpdateTruckStatusRequest extends FormRequest
{
    public function rules()
    {
        return [
            'operational_status' => 'required|in:operational,maintenance,out_of_service',
            'notes' => 'nullable|string'
        ];
    }
}
```

#### StoreAssignmentRequest
```php
class StoreAssignmentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'truck_id' => 'required|exists:trucks,id',
            'user_id' => 'required|exists:users,id',
            'route_id' => 'required|exists:routes,id',
            'assignment_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string'
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation for truck operational status
            // Custom validation for crew role
            // Custom validation for conflicts
        });
    }
}
```

#### UpdateAssignmentRequest
```php
class UpdateAssignmentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'truck_id' => 'required|exists:trucks,id',
            'user_id' => 'required|exists:users,id',
            'route_id' => 'required|exists:routes,id',
            'assignment_date' => 'required|date',
            'notes' => 'nullable|string'
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation similar to StoreAssignmentRequest
        });
    }
}
```

#### CopyAssignmentsRequest
```php
class CopyAssignmentsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'source_date' => 'required|date',
            'target_date' => 'required|date|after_or_equal:today|different:source_date',
            'truck_ids' => 'nullable|array',
            'truck_ids.*' => 'exists:trucks,id'
        ];
    }
}
```

### 6. Routes

```php
// Administrator routes for truck management
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::resource('trucks', TruckController::class);
    Route::patch('trucks/{truck}/status', [TruckController::class, 'updateStatus']);
    Route::get('trucks/{truck}/history', [TruckController::class, 'history']);
    
    Route::resource('assignments', AssignmentController::class);
    Route::patch('assignments/{assignment}/cancel', [AssignmentController::class, 'cancel']);
    Route::post('assignments/copy', [AssignmentController::class, 'copy']);
    Route::get('assignments/calendar/data', [AssignmentController::class, 'getCalendarData']);
    Route::get('assignments/unassigned-routes', [AssignmentController::class, 'unassignedRoutes']);
    
    Route::get('truck-availability', [TruckAvailabilityController::class, 'index']);
    Route::get('truck-availability/data', [TruckAvailabilityController::class, 'getAvailability']);
});

// Crew routes for viewing assignments
Route::middleware(['auth', 'role:collection_crew'])->prefix('crew')->group(function () {
    Route::get('assignments', [CrewAssignmentController::class, 'index'])->name('crew.assignments');
    Route::get('assignments/upcoming', [CrewAssignmentController::class, 'upcoming']);
});
```

## Data Models

### Truck Operational Status
- **operational**: Truck is available for assignments
- **maintenance**: Truck is undergoing maintenance
- **out_of_service**: Truck is not available

### Assignment Status
- **active**: Assignment is current and valid
- **cancelled**: Assignment has been cancelled

### Conflict Types
- **Truck Conflict**: Truck already assigned to another route on same date
- **Crew Conflict**: Crew member already assigned to another route on same date
- **Truck Not Operational**: Truck status is not operational

## Error Handling

### Truck Management Errors
- **Duplicate Truck Number**: "A truck with this number already exists"
- **Truck Deletion with Future Assignments**: "Cannot delete truck with future assignments. Please cancel or reassign them first"
- **Invalid Capacity**: "Capacity must be a positive number"

### Assignment Errors
- **Truck Conflict**: "This truck is already assigned to another route on this date"
- **Crew Conflict**: "This crew member is already assigned to another route on this date"
- **Truck Not Operational**: "This truck is not operational. Current status: [status]"
- **Invalid Crew Role**: "Selected user is not a collection crew member"
- **Past Date Assignment**: "Cannot create assignments for past dates"
- **Copy Conflicts**: "Some assignments could not be copied due to conflicts: [list]"

### Status Change Errors
- **Status Change with Future Assignments**: "Warning: This truck has [count] future assignments. Changing status may affect operations"

## Testing Strategy

### Unit Tests
- Truck model methods (isOperational, hasAssignmentOn, getUtilizationRate)
- Assignment model methods (isActive, cancel, hasConflictWith)
- AssignmentService business logic methods
- AlertService alert generation methods

### Feature Tests
- Truck CRUD operations by administrator
- Truck status changes with history logging
- Assignment CRUD operations with conflict detection
- Assignment cancellation
- Assignment copying functionality
- Truck availability checking
- Unassigned routes detection
- Crew assignment viewing
- Alert generation

### Integration Tests
- Complete assignment creation flow with validations
- Assignment copying with conflict resolution
- Dashboard alert display
- Truck utilization reporting

## UI/UX Design

### Administrator Views

#### Trucks Index
- Data table with columns: Truck Number, License Plate, Capacity, Status, Actions
- Status badges with color coding (Green=Operational, Amber=Maintenance, Red=Out of Service)
- Search bar for truck number/license plate
- Filter dropdown for operational status
- "Register Truck" button (Teal accent)

#### Truck Create/Edit Form
- Truck number input (required)
- License plate input (required)
- Capacity input with unit label (required)
- Operational status dropdown
- Notes textarea
- Save button (Teal) / Cancel button

#### Truck Status Update Modal
- Current status display
- New status dropdown
- Status change notes textarea
- Warning message if future assignments exist
- Confirm button (Amber for warnings)

#### Truck Details/History View
- Truck information card
- Assignment history table with date, route, crew columns
- Utilization statistics
- Status change history timeline
- "Edit Truck" and "Update Status" buttons

#### Assignments Calendar View
- FullCalendar.js monthly/weekly view
- Each assignment displayed as event with truck number and route
- Color coding by truck or route
- Click event to view/edit assignment details
- Date navigation
- Filter by truck or crew dropdown
- "Create Assignment" button
- "Copy Assignments" button

#### Assignment Create/Edit Form
- Truck selection dropdown (filtered by operational status)
- Crew member selection dropdown (filtered by role)
- Route selection dropdown
- Date picker
- Notes textarea
- Conflict warnings display
- Save button (Teal) / Cancel button

#### Truck Availability View
- Date selector
- Grid or list showing all trucks
- Status indicators (Available, Assigned, Maintenance, Out of Service)
- For assigned trucks, show route and crew
- Quick assign button for available trucks

#### Unassigned Routes View
- Date range selector (default: next 7 days)
- List of routes with scheduled collections but no assignments
- For each route: name, zone, scheduled date, collection time
- "Create Assignment" button for each route
- Warning count badge

### Crew Views

#### My Assignments (Today)
- Large card showing today's assignment
- Truck number and details prominently displayed
- Route name and zone
- Collection time
- Special instructions
- "No assignment today" message if none

#### Upcoming Assignments
- List view of next 14 days
- Grouped by date
- Each assignment shows truck, route, and time
- Empty state if no upcoming assignments

### Dashboard Alerts (Admin)

#### Alert Cards
- Unassigned routes alert (Amber background)
  - Count of routes without assignments in next 3 days
  - "View Unassigned Routes" link
  - Dismiss button
  
- Underutilized trucks alert (Teal background)
  - Count of operational trucks with no assignments in next 7 days
  - "View Truck Availability" link
  - Dismiss button

## Security Considerations

1. **Authorization**
   - Only administrators can manage trucks and assignments
   - Crew can only view their own assignments
   - Prevent assignment viewing across crew members

2. **Input Validation**
   - Validate all date inputs
   - Validate truck operational status before assignment
   - Validate user role (must be collection_crew)
   - Prevent SQL injection through Eloquent

3. **Data Integrity**
   - Prevent truck deletion with future assignments
   - Enforce unique constraints on assignments
   - Validate conflicts at both application and database level
   - Use database transactions for assignment copying

4. **Business Logic Validation**
   - Prevent double-booking of trucks
   - Prevent double-booking of crew members
   - Warn about status changes affecting assignments

## Implementation Notes

1. Use soft deletes for trucks to maintain historical data
2. Implement eager loading to prevent N+1 queries when displaying assignments
3. Use database transactions when copying multiple assignments
4. Consider caching truck availability data for performance
5. Implement database indexes on assignment_date for faster queries
6. Use Carbon for all date calculations
7. Create database seeders for sample trucks and assignments
8. Implement conflict checking at both application and database level (unique constraints)
9. Consider adding truck maintenance scheduling in future iterations
10. Log all status changes for audit trail
11. Implement alert dismissal with user-specific tracking
12. Consider adding assignment notifications in future iterations
