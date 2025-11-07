# Design Document: Collection Logging

## Overview

This design implements a comprehensive collection logging system for SWEEP that enables Collection Crew members to record completed waste collection activities, upload proof photos, report issues, and track their work history. Administrators can monitor collection operations, analyze completion rates, and identify recurring issues. The system uses Laravel's file storage capabilities with Intervention Image for photo processing.

## Architecture

### Technology Stack
- **Framework**: Laravel 11.x
- **Database**: MariaDB
- **Frontend**: Blade templates with Bootstrap 5
- **Image Processing**: Intervention Image
- **File Storage**: Laravel Storage (local disk for MVP)
- **Authorization**: Spatie Laravel Permission (from User Management feature)
- **Dependencies**: Assignment model from Truck Assignment System feature

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  - Collection Logging Views (Crew)                          │
│  - Collection History Views (Crew)                           │
│  - Collection Logs Management (Admin)                        │
│  - Collection Analytics Dashboard (Admin)                    │
│  - Issue Analysis Views (Admin)                              │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  - CollectionLogController (Crew)                           │
│  - AdminCollectionLogController (Admin)                      │
│  - CollectionAnalyticsController (Admin)                     │
│  - Middleware (edit time window validation)                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  - CollectionLog Model                                       │
│  - CollectionPhoto Model                                     │
│  - AdminNote Model                                           │
│  - CollectionLogService (business logic)                     │
│  - PhotoService (image processing)                           │
│  - AnalyticsService (statistics)                             │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  - collection_logs table                                     │
│  - collection_photos table                                   │
│  - admin_notes table                                         │
│  - File Storage (photos)                                     │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### Collection Logs Table
```sql
collection_logs
- id: bigint (PK)
- assignment_id: bigint (FK to assignments) UNIQUE
- completion_time: datetime NULL
- status: enum('pending', 'completed', 'incomplete', 'issue_reported') DEFAULT 'pending'
- issue_type: varchar(100) NULL
- issue_description: text NULL
- completion_percentage: tinyint NULL (0-100)
- crew_notes: text NULL
- created_by: bigint (FK to users)
- created_at: timestamp
- updated_at: timestamp
- edited_at: timestamp NULL

INDEX (assignment_id)
INDEX (status)
INDEX (created_by)
INDEX (created_at)
```

#### Collection Photos Table
```sql
collection_photos
- id: bigint (PK)
- collection_log_id: bigint (FK to collection_logs)
- file_path: varchar(255)
- file_name: varchar(255)
- file_size: integer (in bytes)
- uploaded_at: timestamp
- created_at: timestamp
- updated_at: timestamp

INDEX (collection_log_id)
```

#### Admin Notes Table
```sql
admin_notes
- id: bigint (PK)
- collection_log_id: bigint (FK to collection_logs)
- admin_id: bigint (FK to users)
- note: text
- created_at: timestamp
- updated_at: timestamp

INDEX (collection_log_id)
INDEX (admin_id)
```

### 2. Models

#### CollectionLog Model
```php
class CollectionLog extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'assignment_id',
        'completion_time',
        'status',
        'issue_type',
        'issue_description',
        'completion_percentage',
        'crew_notes',
        'created_by',
        'edited_at'
    ];
    
    protected $casts = [
        'completion_time' => 'datetime',
        'edited_at' => 'datetime',
        'completion_percentage' => 'integer'
    ];
    
    // Constants for status
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INCOMPLETE = 'incomplete';
    public const STATUS_ISSUE_REPORTED = 'issue_reported';
    
    // Constants for issue types
    public const ISSUE_TYPES = [
        'blocked_road' => 'Blocked Road',
        'truck_problem' => 'Truck Problem',
        'weather' => 'Weather Conditions',
        'no_access' => 'No Access to Area',
        'safety_concern' => 'Safety Concern',
        'other' => 'Other'
    ];
    
    // Relationships
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function photos()
    {
        return $this->hasMany(CollectionPhoto::class);
    }
    
    public function adminNotes()
    {
        return $this->hasMany(AdminNote::class);
    }
    
    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
    
    public function scopeWithIssues($query)
    {
        return $query->where('status', self::STATUS_ISSUE_REPORTED);
    }
    
    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereHas('assignment', function($q) use ($start, $end) {
            $q->whereBetween('assignment_date', [$start, $end]);
        });
    }
    
    // Helper methods
    public function isEditable(): bool
    public function canBeEditedBy(User $user): bool
    public function isCompleted(): bool
    public function hasIssue(): bool
    public function getEditTimeRemaining(): ?int
}
```

#### CollectionPhoto Model
```php
class CollectionPhoto extends Model
{
    protected $fillable = [
        'collection_log_id',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_at'
    ];
    
    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size' => 'integer'
    ];
    
    // Relationships
    public function collectionLog()
    {
        return $this->belongsTo(CollectionLog::class);
    }
    
    // Helper methods
    public function getUrl(): string
    public function getThumbnailUrl(): string
    public function getFileSizeFormatted(): string
}
```

#### AdminNote Model
```php
class AdminNote extends Model
{
    protected $fillable = [
        'collection_log_id',
        'admin_id',
        'note'
    ];
    
    // Relationships
    public function collectionLog()
    {
        return $this->belongsTo(CollectionLog::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
```

### 3. Controllers

#### CollectionLogController (Crew)
```php
class CollectionLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:collection_crew']);
    }
    
    public function index()
    // Display today's assignment with logging option
    
    public function create(Assignment $assignment)
    // Show collection logging form
    
    public function store(StoreCollectionLogRequest $request, Assignment $assignment)
    // Create collection log with photos
    
    public function show(CollectionLog $collectionLog)
    // Display collection log details
    
    public function edit(CollectionLog $collectionLog)
    // Show edit form (within 2-hour window)
    
    public function update(UpdateCollectionLogRequest $request, CollectionLog $collectionLog)
    // Update collection log (within 2-hour window)
    
    public function history()
    // Display crew member's collection history
    
    public function uploadPhoto(UploadPhotoRequest $request, CollectionLog $collectionLog)
    // Upload additional photo (AJAX)
    
    public function deletePhoto(CollectionPhoto $photo)
    // Delete photo (within edit window)
}
```

#### AdminCollectionLogController (Admin)
```php
class AdminCollectionLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all collection logs with filters
    
    public function show(CollectionLog $collectionLog)
    // Display detailed collection log view
    
    public function addNote(AddAdminNoteRequest $request, CollectionLog $collectionLog)
    // Add administrative note to log
    
    public function issueAnalysis()
    // Display issue analysis view
    
    public function routeIssues(Route $route)
    // Display all issues for specific route
}
```

#### CollectionAnalyticsController (Admin)
```php
class CollectionAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display collection analytics dashboard
    
    public function getCompletionRates(Request $request)
    // API endpoint for completion rate data (AJAX)
    
    public function getStatusBreakdown(Request $request)
    // API endpoint for status breakdown (AJAX)
    
    public function getCrewPerformance(Request $request)
    // API endpoint for crew performance data (AJAX)
    
    public function getRoutePerformance(Request $request)
    // API endpoint for route performance data (AJAX)
}
```

### 4. Services

#### CollectionLogService
```php
class CollectionLogService
{
    public function createLog(Assignment $assignment, array $data, User $user): CollectionLog
    // Create collection log with validation
    
    public function updateLog(CollectionLog $log, array $data): CollectionLog
    // Update collection log with edit window check
    
    public function canEdit(CollectionLog $log, User $user): bool
    // Check if log can be edited (2-hour window + ownership)
    
    public function getCrewHistory(User $user, ?Carbon $start = null, ?Carbon $end = null): Collection
    // Get collection logs for crew member
    
    public function getLogsWithFilters(array $filters): Collection
    // Get logs with admin filters (date, status, route, crew)
    
    public function getCompletionRate(Carbon $start, Carbon $end, ?array $filters = null): float
    // Calculate completion rate percentage
    
    public function getStatusBreakdown(Carbon $start, Carbon $end, ?array $filters = null): array
    // Get count of logs by status
    
    public function getRoutesWithRecurringIssues(Carbon $start, Carbon $end, int $threshold = 2): Collection
    // Get routes with multiple issues
    
    public function getIssuesByType(Carbon $start, Carbon $end): array
    // Get issue count grouped by type
}
```

#### PhotoService
```php
class PhotoService
{
    public function uploadPhoto(UploadedFile $file, CollectionLog $log): CollectionPhoto
    // Upload and process photo
    
    public function createThumbnail(string $path): string
    // Create thumbnail using Intervention Image
    
    public function deletePhoto(CollectionPhoto $photo): bool
    // Delete photo and thumbnail from storage
    
    public function validatePhotoCount(CollectionLog $log): bool
    // Check if log has less than 5 photos
    
    public function getPhotoUrl(CollectionPhoto $photo): string
    // Get public URL for photo
    
    public function getThumbnailUrl(CollectionPhoto $photo): string
    // Get public URL for thumbnail
}
```

#### AnalyticsService
```php
class AnalyticsService
{
    public function getCompletionTrend(Carbon $start, Carbon $end): array
    // Get daily completion rates for trend chart
    
    public function getCrewPerformance(Carbon $start, Carbon $end): Collection
    // Get performance metrics per crew member
    
    public function getRoutePerformance(Carbon $start, Carbon $end): Collection
    // Get performance metrics per route
    
    public function getAverageCompletionTime(Carbon $start, Carbon $end): float
    // Calculate average time to complete collections
    
    public function getIssueHotspots(Carbon $start, Carbon $end): array
    // Identify routes/zones with most issues
}
```

### 5. Form Requests

#### StoreCollectionLogRequest
```php
class StoreCollectionLogRequest extends FormRequest
{
    public function rules()
    {
        return [
            'completion_time' => 'required_if:status,completed|date',
            'status' => 'required|in:completed,incomplete,issue_reported',
            'issue_type' => 'required_if:status,issue_reported|in:' . implode(',', array_keys(CollectionLog::ISSUE_TYPES)),
            'issue_description' => 'required_if:status,issue_reported|string',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'crew_notes' => 'nullable|string|max:1000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,webp|max:5120' // 5MB
        ];
    }
}
```

#### UpdateCollectionLogRequest
```php
class UpdateCollectionLogRequest extends FormRequest
{
    public function rules()
    {
        return [
            'completion_time' => 'required_if:status,completed|date',
            'status' => 'required|in:completed,incomplete,issue_reported',
            'issue_type' => 'required_if:status,issue_reported|in:' . implode(',', array_keys(CollectionLog::ISSUE_TYPES)),
            'issue_description' => 'required_if:status,issue_reported|string',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'crew_notes' => 'nullable|string|max:1000'
        ];
    }
    
    public function authorize()
    {
        $log = $this->route('collectionLog');
        return $log->canBeEditedBy($this->user());
    }
}
```

#### UploadPhotoRequest
```php
class UploadPhotoRequest extends FormRequest
{
    public function rules()
    {
        return [
            'photo' => 'required|image|mimes:jpeg,png,webp|max:5120'
        ];
    }
    
    public function authorize()
    {
        $log = $this->route('collectionLog');
        return $log->canBeEditedBy($this->user()) && 
               $log->photos()->count() < 5;
    }
}
```

#### AddAdminNoteRequest
```php
class AddAdminNoteRequest extends FormRequest
{
    public function rules()
    {
        return [
            'note' => 'required|string|max:1000'
        ];
    }
}
```

### 6. Middleware

#### EnsureLogIsEditable
```php
class EnsureLogIsEditable
{
    public function handle($request, Closure $next)
    {
        $log = $request->route('collectionLog');
        
        if (!$log->canBeEditedBy($request->user())) {
            return redirect()->back()
                ->with('error', 'This log can no longer be edited.');
        }
        
        return $next($request);
    }
}
```

### 7. Routes

```php
// Crew routes for collection logging
Route::middleware(['auth', 'role:collection_crew'])->prefix('crew')->group(function () {
    Route::get('collections', [CollectionLogController::class, 'index'])->name('crew.collections');
    Route::get('collections/history', [CollectionLogController::class, 'history']);
    Route::get('assignments/{assignment}/log', [CollectionLogController::class, 'create']);
    Route::post('assignments/{assignment}/log', [CollectionLogController::class, 'store']);
    Route::get('collections/{collectionLog}', [CollectionLogController::class, 'show']);
    Route::get('collections/{collectionLog}/edit', [CollectionLogController::class, 'edit'])
        ->middleware('ensure.log.editable');
    Route::patch('collections/{collectionLog}', [CollectionLogController::class, 'update'])
        ->middleware('ensure.log.editable');
    Route::post('collections/{collectionLog}/photos', [CollectionLogController::class, 'uploadPhoto'])
        ->middleware('ensure.log.editable');
    Route::delete('photos/{photo}', [CollectionLogController::class, 'deletePhoto'])
        ->middleware('ensure.log.editable');
});

// Admin routes for collection log management
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::get('collection-logs', [AdminCollectionLogController::class, 'index']);
    Route::get('collection-logs/{collectionLog}', [AdminCollectionLogController::class, 'show']);
    Route::post('collection-logs/{collectionLog}/notes', [AdminCollectionLogController::class, 'addNote']);
    Route::get('collection-logs/issues/analysis', [AdminCollectionLogController::class, 'issueAnalysis']);
    Route::get('routes/{route}/issues', [AdminCollectionLogController::class, 'routeIssues']);
    
    Route::get('analytics/collections', [CollectionAnalyticsController::class, 'index']);
    Route::get('analytics/collections/completion-rates', [CollectionAnalyticsController::class, 'getCompletionRates']);
    Route::get('analytics/collections/status-breakdown', [CollectionAnalyticsController::class, 'getStatusBreakdown']);
    Route::get('analytics/collections/crew-performance', [CollectionAnalyticsController::class, 'getCrewPerformance']);
    Route::get('analytics/collections/route-performance', [CollectionAnalyticsController::class, 'getRoutePerformance']);
});
```

## Data Models

### Collection Log Status
- **pending**: Assignment exists but no log created yet
- **completed**: Collection successfully completed
- **incomplete**: Collection partially completed
- **issue_reported**: Issue encountered during collection

### Issue Types
- **blocked_road**: Road access blocked
- **truck_problem**: Mechanical or operational truck issue
- **weather**: Adverse weather conditions
- **no_access**: Unable to access collection area
- **safety_concern**: Safety hazard identified
- **other**: Other issues not categorized above

### Edit Time Window
- Collection logs can be edited within 2 hours of creation
- After 2 hours, only administrators can modify logs via admin notes

## Error Handling

### Collection Log Errors
- **Assignment Already Logged**: "A collection log already exists for this assignment"
- **Edit Window Expired**: "This log can no longer be edited (2-hour window expired)"
- **Unauthorized Edit**: "You can only edit your own collection logs"
- **Invalid Status**: "Invalid collection status provided"
- **Missing Required Field**: "Issue description is required when reporting an issue"

### Photo Upload Errors
- **File Too Large**: "Photo must be smaller than 5MB"
- **Invalid Format**: "Only JPEG, PNG, and WEBP images are allowed"
- **Photo Limit Exceeded**: "Maximum 5 photos per collection log"
- **Upload Failed**: "Failed to upload photo. Please try again"

### Permission Errors
- **Crew Access Only**: "Only assigned crew members can log collections"
- **Admin Access Required**: "Administrator access required to view all logs"

## Testing Strategy

### Unit Tests
- CollectionLog model methods (isEditable, canBeEditedBy, getEditTimeRemaining)
- CollectionLogService business logic methods
- PhotoService image processing methods
- AnalyticsService calculation methods

### Feature Tests
- Collection log creation by crew
- Photo upload and deletion
- Issue reporting
- Incomplete collection logging
- Edit within time window
- Edit prevention after time window
- Collection history viewing
- Admin log viewing with filters
- Admin note addition
- Completion rate calculations
- Issue analysis

### Integration Tests
- Complete collection logging flow with photos
- Analytics dashboard data generation
- Issue hotspot identification

## UI/UX Design

### Crew Views

#### Today's Assignment (with Logging)
- Assignment card showing truck, route, zone
- Collection time display
- "Log Collection" button (Teal accent) if not logged
- Status badge if already logged
- Link to view log if exists

#### Collection Logging Form
- Status selection (radio buttons): Completed, Incomplete, Issue Reported
- Conditional fields based on status:
  - Completed: completion time picker, notes
  - Incomplete: reason textarea, completion percentage slider, notes
  - Issue Reported: issue type dropdown, description textarea, notes
- Photo upload area (drag & drop or click)
- Photo preview thumbnails with delete option
- Submit button (Teal)
- Cancel button

#### Collection Log View
- Status badge (color-coded)
- Assignment details (truck, route, date)
- Completion time
- Crew notes
- Photo gallery with lightbox
- Issue details if applicable
- Edit button (if within 2-hour window)
- Timestamp display

#### Collection History
- List view of past logs
- Date range filter
- Each log shows: date, route, status, completion time
- Click to view details
- Status color coding

### Admin Views

#### Collection Logs Index
- Data table with columns: Date, Route, Crew, Truck, Status, Completion Time, Actions
- Filters: date range, status, route, crew member
- Status badges with color coding
- Search functionality
- Pagination
- Export button

#### Collection Log Details (Admin)
- Complete log information
- Assignment details
- Crew notes section
- Photo gallery
- Issue details if applicable
- Admin notes section with add note form
- Timestamp information
- Edit history if applicable

#### Collection Analytics Dashboard
- Date range selector
- Key metrics cards:
  - Total collections
  - Completion rate percentage
  - Average completion time
  - Issue count
- Completion trend chart (line chart)
- Status breakdown (pie chart)
- Crew performance table
- Route performance table
- Issue hotspots map/list

#### Issue Analysis View
- Date range selector
- Issue type breakdown (bar chart)
- Routes with recurring issues table
- Issue count per route
- Link to view all issues for route
- Filter by issue type

#### Route Issues View
- Route information header
- List of all issues for route
- Date, crew, issue type, description
- Photos if available
- Admin notes
- Timeline view option

## Security Considerations

1. **Authorization**
   - Only assigned crew can log collections
   - Crew can only edit their own logs within time window
   - Only administrators can view all logs and add admin notes
   - Photo access restricted to authenticated users

2. **File Upload Security**
   - Validate file types (whitelist: jpeg, png, webp)
   - Validate file size (max 5MB)
   - Store files outside public directory
   - Generate unique filenames to prevent overwrites
   - Sanitize filenames

3. **Input Validation**
   - Validate all form inputs
   - Sanitize text inputs to prevent XSS
   - Validate date/time inputs
   - Validate percentage values (0-100)

4. **Data Integrity**
   - Prevent duplicate logs for same assignment
   - Enforce edit time window at application and middleware level
   - Track edit timestamps
   - Maintain audit trail with admin notes

## Implementation Notes

1. Use Laravel Storage facade for file management
2. Configure storage disk for collection photos
3. Use Intervention Image for thumbnail generation (200x200px)
4. Implement eager loading to prevent N+1 queries
5. Use database transactions when creating logs with photos
6. Consider indexing status and created_at columns for performance
7. Implement soft deletes if needed for audit trail
8. Create database seeders for sample collection logs
9. Use Carbon for all date/time calculations and edit window checks
10. Implement photo cleanup job for orphaned files
11. Consider adding photo compression for storage optimization
12. Cache analytics data for performance
13. Implement pagination for large result sets
14. Use queues for photo processing if needed in future
15. Consider adding GPS coordinates for collection verification in future iterations
