# Design Document: Resident Reporting System

## Overview

This design implements a comprehensive resident reporting system for SWEEP that enables residents to submit complaints about waste collection issues, upload supporting photos, and track report status. Administrators can review reports, update status, add responses, assign reports to routes or crews, and analyze reporting patterns to identify service gaps. The system uses Laravel's file storage with Intervention Image for photo processing and provides analytics for performance monitoring.

## Architecture

### Technology Stack
- **Framework**: Laravel 11.x
- **Database**: MariaDB
- **Frontend**: Blade templates with Bootstrap 5
- **Image Processing**: Intervention Image
- **File Storage**: Laravel Storage (local disk for MVP)
- **Charts**: Chart.js for analytics visualization
- **Authorization**: Spatie Laravel Permission (from User Management feature)

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  - Report Submission Views (Resident)                       │
│  - Report Tracking Views (Resident)                          │
│  - Report Management Views (Admin)                           │
│  - Report Analytics Dashboard (Admin)                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     Application Layer                        │
│  - ResidentReportController (Resident)                      │
│  - AdminReportController (Admin)                             │
│  - ReportAnalyticsController (Admin)                         │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       Domain Layer                           │
│  - Report Model                                              │
│  - ReportPhoto Model                                         │
│  - ReportResponse Model                                      │
│  - ReportStatusHistory Model                                 │
│  - ReportService (business logic)                            │
│  - ReportPhotoService (image processing)                     │
│  - ReportAnalyticsService (statistics)                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  - reports table                                             │
│  - report_photos table                                       │
│  - report_responses table                                    │
│  - report_status_history table                               │
│  - File Storage (photos)                                     │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. Database Schema

#### Reports Table
```sql
reports
- id: bigint (PK)
- reference_number: varchar(20) UNIQUE
- resident_id: bigint (FK to users)
- report_type: enum('missed_pickup', 'uncollected_waste', 'illegal_dumping', 'other')
- location: varchar(255)
- description: text
- status: enum('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending'
- route_id: bigint NULL (FK to routes)
- assigned_to: bigint NULL (FK to users)
- resolved_at: timestamp NULL
- created_at: timestamp
- updated_at: timestamp

INDEX (reference_number)
INDEX (resident_id)
INDEX (status)
INDEX (report_type)
INDEX (created_at)
INDEX (route_id)
INDEX (assigned_to)
```

#### Report Photos Table
```sql
report_photos
- id: bigint (PK)
- report_id: bigint (FK to reports)
- file_path: varchar(255)
- file_name: varchar(255)
- file_size: integer (in bytes)
- uploaded_at: timestamp
- created_at: timestamp
- updated_at: timestamp

INDEX (report_id)
```

#### Report Responses Table
```sql
report_responses
- id: bigint (PK)
- report_id: bigint (FK to reports)
- admin_id: bigint (FK to users)
- response: text
- created_at: timestamp
- updated_at: timestamp

INDEX (report_id)
INDEX (admin_id)
```

#### Report Status History Table
```sql
report_status_history
- id: bigint (PK)
- report_id: bigint (FK to reports)
- old_status: enum('pending', 'in_progress', 'resolved', 'closed') NULL
- new_status: enum('pending', 'in_progress', 'resolved', 'closed')
- changed_by: bigint (FK to users)
- note: text NULL
- created_at: timestamp

INDEX (report_id)
INDEX (changed_by)
```

### 2. Models

#### Report Model
```php
class Report extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'reference_number',
        'resident_id',
        'report_type',
        'location',
        'description',
        'status',
        'route_id',
        'assigned_to',
        'resolved_at'
    ];
    
    protected $casts = [
        'resolved_at' => 'datetime'
    ];
    
    // Constants for report types
    public const TYPE_MISSED_PICKUP = 'missed_pickup';
    public const TYPE_UNCOLLECTED_WASTE = 'uncollected_waste';
    public const TYPE_ILLEGAL_DUMPING = 'illegal_dumping';
    public const TYPE_OTHER = 'other';
    
    public const REPORT_TYPES = [
        self::TYPE_MISSED_PICKUP => 'Missed Pickup',
        self::TYPE_UNCOLLECTED_WASTE => 'Uncollected Waste',
        self::TYPE_ILLEGAL_DUMPING => 'Illegal Dumping',
        self::TYPE_OTHER => 'Other'
    ];
    
    // Constants for status
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';
    
    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_CLOSED => 'Closed'
    ];
    
    // Relationships
    public function resident()
    {
        return $this->belongsTo(User::class, 'resident_id');
    }
    
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
    
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function photos()
    {
        return $this->hasMany(ReportPhoto::class);
    }
    
    public function responses()
    {
        return $this->hasMany(ReportResponse::class);
    }
    
    public function statusHistory()
    {
        return $this->hasMany(ReportStatusHistory::class);
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }
    
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }
    
    public function scopeForDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
    
    // Helper methods
    public function isPending(): bool
    public function isResolved(): bool
    public function getResolutionTime(): ?int
    public function getLatestStatusChange(): ?ReportStatusHistory
    public static function generateReferenceNumber(): string
}
```

#### ReportPhoto Model
```php
class ReportPhoto extends Model
{
    protected $fillable = [
        'report_id',
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
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
    
    // Helper methods
    public function getUrl(): string
    public function getThumbnailUrl(): string
    public function getFileSizeFormatted(): string
}
```

#### ReportResponse Model
```php
class ReportResponse extends Model
{
    protected $fillable = [
        'report_id',
        'admin_id',
        'response'
    ];
    
    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
```

#### ReportStatusHistory Model
```php
class ReportStatusHistory extends Model
{
    protected $fillable = [
        'report_id',
        'old_status',
        'new_status',
        'changed_by',
        'note'
    ];
    
    public $timestamps = false;
    
    protected $casts = [
        'created_at' => 'datetime'
    ];
    
    // Relationships
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
    
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
```

### 3. Controllers

#### ResidentReportController (Resident)
```php
class ResidentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:resident']);
    }
    
    public function index()
    // Display resident's reports with filters
    
    public function create()
    // Show report submission form
    
    public function store(StoreReportRequest $request)
    // Create report with photos
    
    public function show(Report $report)
    // Display report details (authorize: own reports only)
    
    public function search(Request $request)
    // Search reports by reference number
}
```

#### AdminReportController (Admin)
```php
class AdminReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display all reports with filters
    
    public function show(Report $report)
    // Display detailed report view
    
    public function updateStatus(UpdateStatusRequest $request, Report $report)
    // Update report status with note
    
    public function addResponse(AddResponseRequest $request, Report $report)
    // Add administrator response
    
    public function assign(AssignReportRequest $request, Report $report)
    // Assign report to route or crew
    
    public function unassign(Report $report)
    // Remove assignment
}
```

#### ReportAnalyticsController (Admin)
```php
class ReportAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }
    
    public function index()
    // Display analytics dashboard
    
    public function locationAnalysis()
    // Display reports grouped by location
    
    public function typeAnalysis()
    // Display reports grouped by type
    
    public function getTypeDistribution(Request $request)
    // API endpoint for type distribution data (AJAX)
    
    public function getResolutionTimes(Request $request)
    // API endpoint for resolution time data (AJAX)
    
    public function getStatusTrend(Request $request)
    // API endpoint for status trend data (AJAX)
}
```

### 4. Services

#### ReportService
```php
class ReportService
{
    public function createReport(array $data, User $resident): Report
    // Create report with unique reference number
    
    public function updateStatus(Report $report, string $newStatus, User $admin, ?string $note = null): void
    // Update status and record history
    
    public function addResponse(Report $report, string $response, User $admin): ReportResponse
    // Add administrator response
    
    public function assignReport(Report $report, ?int $routeId, ?int $userId): void
    // Assign report to route or crew
    
    public function getResidentReports(User $resident, ?array $filters = null): Collection
    // Get reports for specific resident with filters
    
    public function getReportsWithFilters(array $filters): Collection
    // Get reports with admin filters
    
    public function searchByReference(string $referenceNumber, User $resident): ?Report
    // Search resident's reports by reference number
    
    public function getReportsByLocation(Carbon $start, Carbon $end): Collection
    // Group reports by location
    
    public function getReportsByType(Carbon $start, Carbon $end): array
    // Get count and percentage by type
    
    public function getAverageResolutionTime(Carbon $start, Carbon $end): float
    // Calculate average resolution time in hours
    
    public function getResolutionTimeByType(Carbon $start, Carbon $end): array
    // Get average resolution time per type
    
    public function getOverdueReports(int $targetHours = 48): Collection
    // Get reports exceeding target resolution time
}
```

#### ReportPhotoService
```php
class ReportPhotoService
{
    public function uploadPhoto(UploadedFile $file, Report $report): ReportPhoto
    // Upload and process photo
    
    public function createThumbnail(string $path): string
    // Create thumbnail using Intervention Image
    
    public function deletePhoto(ReportPhoto $photo): bool
    // Delete photo and thumbnail from storage
    
    public function validatePhotoCount(Report $report): bool
    // Check if report has less than 3 photos
    
    public function getPhotoUrl(ReportPhoto $photo): string
    // Get public URL for photo
    
    public function getThumbnailUrl(ReportPhoto $photo): string
    // Get public URL for thumbnail
}
```

#### ReportAnalyticsService
```php
class ReportAnalyticsService
{
    public function getReportTrend(Carbon $start, Carbon $end): array
    // Get daily report submission counts
    
    public function getStatusDistribution(Carbon $start, Carbon $end): array
    // Get count of reports by status
    
    public function getTypeDistribution(Carbon $start, Carbon $end): array
    // Get count and percentage by type
    
    public function getResolutionTimeTrend(Carbon $start, Carbon $end): array
    // Get average resolution time over time
    
    public function getLocationHotspots(Carbon $start, Carbon $end, int $threshold = 3): Collection
    // Identify locations with multiple reports
    
    public function getPerformanceMetrics(Carbon $start, Carbon $end): array
    // Calculate key performance indicators
}
```

### 5. Form Requests

#### StoreReportRequest
```php
class StoreReportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'report_type' => 'required|in:' . implode(',', array_keys(Report::REPORT_TYPES)),
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|mimes:jpeg,png,webp|max:5120' // 5MB
        ];
    }
}
```

#### UpdateStatusRequest
```php
class UpdateStatusRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status' => 'required|in:' . implode(',', array_keys(Report::STATUSES)),
            'note' => 'required|string|max:1000'
        ];
    }
}
```

#### AddResponseRequest
```php
class AddResponseRequest extends FormRequest
{
    public function rules()
    {
        return [
            'response' => 'required|string|max:1000'
        ];
    }
}
```

#### AssignReportRequest
```php
class AssignReportRequest extends FormRequest
{
    public function rules()
    {
        return [
            'route_id' => 'nullable|exists:routes,id',
            'assigned_to' => 'nullable|exists:users,id'
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that at least one assignment is provided
            // Validate that assigned_to is a collection_crew user
        });
    }
}
```

### 6. Routes

```php
// Resident routes for report submission and tracking
Route::middleware(['auth', 'role:resident'])->prefix('resident')->group(function () {
    Route::get('reports', [ResidentReportController::class, 'index'])->name('resident.reports');
    Route::get('reports/create', [ResidentReportController::class, 'create']);
    Route::post('reports', [ResidentReportController::class, 'store']);
    Route::get('reports/search', [ResidentReportController::class, 'search']);
    Route::get('reports/{report}', [ResidentReportController::class, 'show']);
});

// Admin routes for report management
Route::middleware(['auth', 'role:administrator'])->prefix('admin')->group(function () {
    Route::get('reports', [AdminReportController::class, 'index']);
    Route::get('reports/{report}', [AdminReportController::class, 'show']);
    Route::patch('reports/{report}/status', [AdminReportController::class, 'updateStatus']);
    Route::post('reports/{report}/responses', [AdminReportController::class, 'addResponse']);
    Route::patch('reports/{report}/assign', [AdminReportController::class, 'assign']);
    Route::patch('reports/{report}/unassign', [AdminReportController::class, 'unassign']);
    
    Route::get('analytics/reports', [ReportAnalyticsController::class, 'index']);
    Route::get('analytics/reports/location', [ReportAnalyticsController::class, 'locationAnalysis']);
    Route::get('analytics/reports/type', [ReportAnalyticsController::class, 'typeAnalysis']);
    Route::get('analytics/reports/type-distribution', [ReportAnalyticsController::class, 'getTypeDistribution']);
    Route::get('analytics/reports/resolution-times', [ReportAnalyticsController::class, 'getResolutionTimes']);
    Route::get('analytics/reports/status-trend', [ReportAnalyticsController::class, 'getStatusTrend']);
});
```

## Data Models

### Report Types
- **missed_pickup**: Scheduled collection did not occur
- **uncollected_waste**: Waste left behind after collection
- **illegal_dumping**: Unauthorized waste disposal
- **other**: Other waste management issues

### Report Status
- **pending**: Report submitted, awaiting review
- **in_progress**: Report being addressed
- **resolved**: Issue resolved
- **closed**: Report closed (resolved or no action needed)

### Reference Number Format
- Format: `REP-YYYYMMDD-XXXX`
- Example: `REP-20251107-0001`
- YYYY: Year, MM: Month, DD: Day, XXXX: Sequential number

## Error Handling

### Report Submission Errors
- **Missing Required Field**: "Please provide [field name]"
- **Invalid Report Type**: "Please select a valid report type"
- **Photo Upload Failed**: "Failed to upload photo. Please try again"
- **Photo Too Large**: "Photo must be smaller than 5MB"
- **Too Many Photos**: "Maximum 3 photos per report"

### Report Access Errors
- **Unauthorized Access**: "You can only view your own reports"
- **Report Not Found**: "Report not found or you don't have access"

### Status Update Errors
- **Invalid Status**: "Invalid status provided"
- **Missing Note**: "Please provide a note when updating status"

### Assignment Errors
- **Invalid Route**: "Selected route does not exist"
- **Invalid User**: "Selected user is not a collection crew member"
- **No Assignment**: "Please provide at least a route or crew member"

## Testing Strategy

### Unit Tests
- Report model methods (isPending, isResolved, getResolutionTime, generateReferenceNumber)
- ReportService business logic methods
- ReportPhotoService image processing methods
- ReportAnalyticsService calculation methods

### Feature Tests
- Report submission by resident
- Photo upload with reports
- Report listing and filtering (resident)
- Report search by reference number
- Report viewing (resident - own reports only)
- Report listing and filtering (admin)
- Status updates with history
- Response addition
- Report assignment
- Location analysis
- Type analysis
- Resolution time calculations

### Integration Tests
- Complete report submission flow with photos
- Status update flow with notifications
- Analytics dashboard data generation

## UI/UX Design

### Resident Views

#### Report Submission Form
- Report type selection (radio buttons or dropdown)
- Location input field
- Description textarea (max 2000 characters)
- Photo upload area (drag & drop, max 3 photos)
- Photo preview thumbnails
- Submit button (Teal accent)
- Cancel button

#### Report Submission Success
- Display reference number prominently
- Success message
- Link to view report
- Link to submit another report

#### My Reports List
- List view of all reports
- Display: reference number, type, location, date, status
- Status badges with color coding
- Filter by status dropdown
- Search by reference number
- Click to view details
- Newest first ordering

#### Report Details View
- Reference number header
- Status badge (color-coded)
- Report type and location
- Submission date
- Description
- Photo gallery with lightbox
- Status history timeline
- Administrator responses section
- Assignment information if assigned

### Admin Views

#### Reports Index
- Data table with columns: Reference, Resident, Type, Location, Date, Status, Actions
- Filters: date range, status, report type
- Search by reference number or resident name
- Status badges with color coding
- Pagination
- Export button

#### Report Details (Admin)
- Reference number and status header
- Resident information (name, contact)
- Report details (type, location, description, date)
- Photo gallery
- Status history timeline
- Update status form with note
- Add response form
- Assignment section with route/crew selection
- Action buttons (Update Status, Add Response, Assign)

#### Report Analytics Dashboard
- Date range selector
- Key metrics cards:
  - Total reports
  - Pending reports
  - Average resolution time
  - Resolution rate
- Report trend chart (line chart)
- Type distribution chart (pie chart)
- Status distribution chart (bar chart)
- Resolution time by type table
- Overdue reports list

#### Location Analysis View
- Date range selector
- Map or list of locations with report counts
- Highlight hotspots (locations with multiple reports)
- Click location to view all reports
- Sort by report count

#### Type Analysis View
- Date range selector
- Type distribution chart (pie or bar chart)
- Table showing count and percentage per type
- Average resolution time per type
- Click type to view all reports of that type

## Security Considerations

1. **Authorization**
   - Residents can only view their own reports
   - Only administrators can update status and add responses
   - Only administrators can assign reports
   - Photo access restricted to report owner and administrators

2. **File Upload Security**
   - Validate file types (whitelist: jpeg, png, webp)
   - Validate file size (max 5MB)
   - Store files outside public directory
   - Generate unique filenames
   - Sanitize filenames

3. **Input Validation**
   - Validate all form inputs
   - Sanitize text inputs to prevent XSS
   - Validate report type and status enums
   - Limit description length

4. **Data Privacy**
   - Residents cannot see other residents' reports
   - Resident contact information only visible to administrators
   - Implement proper authorization checks

## Implementation Notes

1. Use Laravel Storage facade for file management
2. Configure storage disk for report photos
3. Use Intervention Image for thumbnail generation (200x200px)
4. Implement eager loading to prevent N+1 queries
5. Use database transactions when creating reports with photos
6. Generate unique reference numbers using date + sequential counter
7. Consider indexing reference_number, status, and created_at columns
8. Create database seeders for sample reports
9. Use Carbon for all date/time calculations
10. Implement photo cleanup job for orphaned files
11. Cache analytics data for performance
12. Implement pagination for large result sets
13. Consider adding email notifications in future iterations
14. Consider adding SMS notifications for status updates in future
15. Consider adding geolocation for precise location tracking in future
