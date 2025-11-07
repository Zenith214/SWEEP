# Recycling Tracker Design Document

## Overview

The Recycling Tracker feature enables Collection Crew members to record recyclable materials collected during their routes and provides Administrators with comprehensive analytics and reporting capabilities. The feature integrates seamlessly with the existing SWEEP system architecture, leveraging Laravel's MVC pattern, Eloquent ORM, and Spatie Permission for role-based access control.

The system supports six material types (plastic, paper, glass, metal, cardboard, organic) and provides comprehensive analytics including material type breakdowns, zone performance, crew performance, trend analysis, and target tracking. Collection Crew members can create and edit recycling logs within a two-hour window, while Administrators have full visibility into all recycling data with advanced filtering, reporting, and export capabilities.

## 
Architecture

### System Integration

The Recycling Tracker integrates with existing SWEEP components:

- **User Management**: Leverages existing User model with Spatie Permission roles (collection_crew, administrator)
- **Route System**: Links recycling logs to existing Route model for zone-based analytics
- **Assignment System**: Associates recycling logs with Assignment model to track crew activities
- **Dashboard**: Extends existing admin and crew dashboards with recycling-specific views

### Technology Stack

- **Backend**: Laravel (existing framework)
- **Database**: MariaDB (existing database)
- **Charts**: Laravel Charts package for data visualization
- **Authentication**: Laravel Breeze (existing)
- **Authorization**: Spatie Laravel Permission (existing)
- **Frontend**: Blade templates with Bootstrap 5 (existing UI framework)
- **Export**: Laravel's built-in CSV response capabilities

### Design Rationale

**Polymorphic Material Storage**: We use a pivot table approach for material types rather than separate columns. This provides flexibility for future material type additions and simplifies queries for material-specific analytics.

**Edit Window Implementation**: The two-hour edit window is enforced at the application level using timestamp comparison. This balances data integrity with practical error correction needs.

**Soft Deletes**: Recycling logs use soft deletes to maintain data integrity for historical reporting while allowing administrators to remove erroneous entries.

**Caching Strategy**: Dashboard statistics and trend data are cached for 15 minutes to improve performance, with cache invalidation on new log creation.

## Components and Interfaces

### Models

#### RecyclingLog Model

Primary model for storing recycling collection records.

**Table**: `recycling_logs`

**Columns**:
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key to users)
- `assignment_id` (bigint, nullable, foreign key to assignments)
- `route_id` (bigint, nullable, foreign key to routes)
- `collection_date` (date)
- `notes` (text, nullable, max 500 chars)
- `quality_issue` (boolean, default false)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable)

**Relationships**:
- `belongsTo(User::class)` - Creator of the log
- `belongsTo(Assignment::class)` - Associated assignment (nullable)
- `belongsTo(Route::class)` - Associated route (nullable)
- `hasMany(RecyclingLogMaterial::class)` - Material entries

**Key Methods**:
- `isWithinEditWindow()`: Returns boolean if current time is within 2 hours of creation
- `canBeEditedBy(User $user)`: Checks if user can edit (creator + within window)
- `getTotalWeight()`: Calculates sum of all material weights
- `getMaterialBreakdown()`: Returns array of materials with weights and percentages

**Scopes**:
- `forUser(User $user)`: Filter by creator
- `forDateRange($start, $end)`: Filter by collection date range
- `forRoute(Route $route)`: Filter by route
- `forZone($zone)`: Filter by route zone
- `withQualityIssues()`: Filter logs marked with quality issues
- `recent()`: Order by collection_date DESC

#### RecyclingLogMaterial Model

Pivot model for material types and weights.

**Table**: `recycling_log_materials`

**Columns**:
- `id` (bigint, primary key)
- `recycling_log_id` (bigint, foreign key)
- `material_type` (enum: plastic, paper, glass, metal, cardboard, organic)
- `weight` (decimal(8,2)) - Weight in kilograms
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships**:
- `belongsTo(RecyclingLog::class)`

**Validation Rules**:
- `material_type`: required, in:plastic,paper,glass,metal,cardboard,organic
- `weight`: required, numeric, min:0.01, max:10000

**Design Rationale**: Separate table allows multiple materials per log and simplifies material-specific queries. Enum constraint ensures data consistency.

#### RecyclingTarget Model

Stores monthly recycling targets for performance tracking.

**Table**: `recycling_targets`

**Columns**:
- `id` (bigint, primary key)
- `material_type` (enum: plastic, paper, glass, metal, cardboard, organic, all, nullable)
- `target_weight` (decimal(10,2)) - Target in kilograms
- `month` (date) - First day of target month
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Key Methods**:
- `getCurrentProgress()`: Calculates percentage of target achieved
- `isAchieved()`: Returns boolean if target met or exceeded

**Design Rationale**: `material_type` nullable with special value 'all' for total recyclables target. Month stored as date for easy querying.

### Controllers

#### RecyclingLogController

Handles CRUD operations for recycling logs (Collection Crew).

**Routes**:
- `GET /crew/recycling-logs` - Index view (list crew's logs)
- `GET /crew/recycling-logs/create` - Create form
- `POST /crew/recycling-logs` - Store new log
- `GET /crew/recycling-logs/{id}/edit` - Edit form (within window)
- `PUT /crew/recycling-logs/{id}` - Update log (within window)

**Middleware**: `auth`, `role:collection_crew`

**Key Methods**:
- `index()`: Display crew member's logs with date filtering
- `create()`: Show form with active assignment pre-filled
- `store(RecyclingLogRequest $request)`: Validate and create log with materials
- `edit(RecyclingLog $log)`: Show edit form (check edit window)
- `update(RecyclingLogRequest $request, RecyclingLog $log)`: Update log (check edit window)

**Design Rationale**: Separate controller for crew vs admin maintains clear separation of concerns and simplifies authorization logic.

#### Admin\RecyclingLogController

Handles recycling log management for administrators.

**Routes**:
- `GET /admin/recycling-logs` - Index view with advanced filtering
- `GET /admin/recycling-logs/{id}` - Show individual log details
- `GET /admin/recycling-logs/export` - Export filtered data to CSV

**Middleware**: `auth`, `role:administrator`

**Key Methods**:
- `index()`: Display all logs with filters (date, material, route, zone, quality issues)
- `show(RecyclingLog $log)`: Display detailed log view with edit history
- `export()`: Generate CSV file with filtered data

#### Admin\RecyclingAnalyticsController

Handles analytics and reporting for administrators.

**Routes**:
- `GET /admin/recycling/analytics` - Main analytics dashboard
- `GET /admin/recycling/analytics/materials` - Material breakdown view
- `GET /admin/recycling/analytics/zones` - Zone performance view
- `GET /admin/recycling/analytics/trends` - Trend analysis view
- `GET /admin/recycling/analytics/crew` - Crew performance view

**Middleware**: `auth`, `role:administrator`

**Key Methods**:
- `dashboard()`: Overview with key metrics and charts
- `materialAnalysis()`: Material type breakdown with charts
- `zonePerformance()`: Zone-based analytics with highlighting
- `trendAnalysis()`: Time-series data with configurable intervals
- `crewPerformance()`: Crew member statistics and rankings

**Design Rationale**: Separate analytics controller keeps reporting logic isolated and cacheable. Each method returns view with pre-computed statistics.

#### Admin\RecyclingTargetController

Manages recycling targets.

**Routes**:
- `GET /admin/recycling/targets` - List targets with progress
- `POST /admin/recycling/targets` - Create new target
- `PUT /admin/recycling/targets/{id}` - Update target
- `DELETE /admin/recycling/targets/{id}` - Delete target

**Middleware**: `auth`, `role:administrator`

### Services

#### RecyclingAnalyticsService

Centralized service for analytics calculations with caching.

**Key Methods**:
- `getMaterialTotals($startDate, $endDate)`: Returns array of material types with totals and percentages
- `getZonePerformance($startDate, $endDate, $materialTypes)`: Returns zones with totals, sorted by weight
- `getTrendData($startDate, $endDate, $interval, $materialTypes)`: Returns time-series data
- `getCrewPerformance($startDate, $endDate)`: Returns crew statistics
- `getRecyclingRate($startDate, $endDate)`: Calculates various rate metrics
- `compareWithPreviousPeriod($startDate, $endDate)`: Returns percentage change
- `getTargetProgress($month)`: Returns all targets with current progress

**Caching Strategy**: 
- Cache key includes date range and filter parameters
- 15-minute TTL for dashboard data
- Cache invalidated on new log creation or update

**Design Rationale**: Service layer separates business logic from controllers, enables reusability, and centralizes caching logic.

#### RecyclingExportService

Handles CSV export generation.

**Key Methods**:
- `exportLogs($logs, $filename)`: Generates CSV response with proper headers
- `formatLogForExport(RecyclingLog $log)`: Converts log to CSV row format

**Design Rationale**: Dedicated export service allows for future format additions (PDF, Excel) and keeps export logic testable.

### Form Requests

#### RecyclingLogRequest

Validates recycling log creation and updates.

**Validation Rules**:
```php
'collection_date' => 'required|date|before_or_equal:today'
'notes' => 'nullable|string|max:500'
'quality_issue' => 'boolean'
'materials' => 'required|array|min:1|max:6'
'materials.*.material_type' => 'required|in:plastic,paper,glass,metal,cardboard,organic'
'materials.*.weight' => 'required|numeric|min:0.01|max:10000'
```

**Custom Validation**:
- Ensure unique material types within single log
- Validate edit window for updates
- Validate user owns log for updates

**Design Rationale**: Form request keeps validation logic separate and reusable, with custom rules for business logic.

## Data Models

### Entity Relationship Diagram

```
Users (existing)
  |
  |-- 1:N --> RecyclingLogs
  |
  |-- 1:N --> Assignments (existing)
                  |
                  |-- 1:N --> RecyclingLogs

Routes (existing)
  |
  |-- 1:N --> RecyclingLogs

RecyclingLogs
  |
  |-- 1:N --> RecyclingLogMaterials

RecyclingTargets (standalone)
```

### Data Flow

**Creating a Recycling Log**:
1. Crew member accesses create form
2. System pre-fills route/assignment if active assignment exists
3. Crew member selects 1-6 material types and enters weights
4. Form submission validates data
5. Transaction creates RecyclingLog and related RecyclingLogMaterial records
6. Cache invalidated for affected analytics
7. Success message displayed, redirect to index

**Viewing Analytics**:
1. Administrator accesses analytics dashboard
2. System checks cache for requested data
3. If cache miss, RecyclingAnalyticsService computes statistics
4. Results cached with 15-minute TTL
5. View rendered with charts and tables

**Exporting Data**:
1. Administrator applies filters and clicks export
2. System queries filtered RecyclingLogs with eager loading
3. RecyclingExportService formats data as CSV
4. Browser downloads file with descriptive filename

## Error Handling

### Validation Errors

**Client-Side**:
- Real-time weight validation (min/max)
- Material type uniqueness check
- Date validation (not future dates)

**Server-Side**:
- Form request validation with detailed error messages
- Redirect back with old input on validation failure
- Display errors using Bootstrap alert components

### Authorization Errors

**Edit Window Expired**:
- Check performed in controller before showing edit form
- Display friendly message: "This log can no longer be edited (2-hour window expired)"
- Redirect to index view

**Unauthorized Access**:
- Middleware prevents access to wrong role routes
- 403 error page for direct URL access attempts
- Policy checks for log ownership

### Database Errors

**Foreign Key Violations**:
- Soft deletes prevent cascade issues
- Validation ensures referenced records exist

**Concurrent Updates**:
- Optimistic locking not required (edit window limits conflicts)
- Last-write-wins approach acceptable for this use case

### System Errors

**Export Failures**:
- Try-catch around export generation
- Log error details
- Display user-friendly error message
- Offer retry option

**Cache Failures**:
- Graceful degradation (compute without cache)
- Log cache errors for monitoring
- Don't block user operations

## Testing Strategy

### Unit Tests

**Models**:
- `RecyclingLog::isWithinEditWindow()` with various timestamps
- `RecyclingLog::getTotalWeight()` calculation accuracy
- `RecyclingLog::getMaterialBreakdown()` percentage calculations
- `RecyclingTarget::getCurrentProgress()` with various scenarios
- Model scopes return correct filtered results

**Services**:
- `RecyclingAnalyticsService` methods with known datasets
- Date range calculations
- Percentage change calculations
- Cache key generation

**Form Requests**:
- Validation rules with valid/invalid data
- Custom validation logic
- Edit window validation

### Feature Tests

**Recycling Log CRUD**:
- Crew member can create log with valid data
- Crew member cannot create log with invalid data
- Crew member can edit own log within window
- Crew member cannot edit log after window expires
- Crew member cannot edit another crew member's log
- Crew member can view own logs filtered by date

**Admin Analytics**:
- Administrator can view all logs with filters
- Material breakdown calculates correctly
- Zone performance highlights high performers
- Trend data aggregates by interval correctly
- Crew performance ranks correctly
- Export generates valid CSV with correct data

**Authorization**:
- Collection crew cannot access admin routes
- Administrator cannot access crew-specific routes
- Unauthenticated users redirected to login

### Integration Tests

**Assignment Integration**:
- Creating log with active assignment links correctly
- Log displays route information from assignment
- Zone analytics group by assignment route

**Target Tracking**:
- Target progress updates when logs created
- Target achievement highlighting works
- Multiple targets (material-specific + total) calculate independently

### Browser Tests (Optional)

**User Workflows**:
- Complete flow: login as crew → create log → view logs → edit log
- Complete flow: login as admin → view analytics → apply filters → export
- Form interactions: add/remove materials, weight validation
- Chart rendering and interactions

**Design Rationale**: Testing strategy focuses on business logic and critical paths. Browser tests optional for MVP but recommended for production. Feature tests provide good coverage of integration points.


## User Interface Design

### Collection Crew Views

#### Recycling Logs Index (`/crew/recycling-logs`)

**Layout**:
- Page header: "My Recycling Logs"
- Filter panel: Date range picker (start date, end date)
- Table with columns:
  - Collection Date
  - Route/Zone
  - Materials (badges for each type)
  - Total Weight (kg)
  - Actions (View, Edit if within window)
- Pagination (20 logs per page)
- "Create New Log" button (primary action, top right)

**Visual Design**:
- Material type badges color-coded (plastic=blue, paper=brown, glass=green, metal=gray, cardboard=tan, organic=lime)
- Edit button disabled with tooltip if window expired
- Empty state: "No recycling logs found. Start by creating your first log!"

#### Create/Edit Recycling Log Form

**Layout**:
- Form sections:
  1. **Collection Details**
     - Collection Date (date picker, default: today, max: today)
     - Route/Zone (auto-filled from active assignment, read-only if assigned)
  
  2. **Materials Collected**
     - Dynamic material entry rows
     - Each row: Material Type dropdown + Weight input (kg)
     - "Add Material" button (max 6 materials)
     - "Remove" button for each row
     - Total weight display (auto-calculated, bold)
  
  3. **Additional Information**
     - Notes textarea (optional, 500 char limit with counter)
     - Quality Issue checkbox ("Mark if contamination or quality issues observed")
  
  4. **Actions**
     - "Save Recycling Log" button (primary)
     - "Cancel" button (secondary)

**Validation Feedback**:
- Inline error messages below fields
- Weight validation: "Weight must be between 0.01 and 10,000 kg"
- Material uniqueness: "This material type is already selected"
- Success message: "Recycling log saved successfully!"

**Edit Mode Differences**:
- Page header shows: "Edit Recycling Log (Editable until [timestamp])"
- Warning banner if approaching 2-hour limit: "You have X minutes remaining to edit this log"

### Administrator Views

#### Recycling Logs Index (`/admin/recycling-logs`)

**Layout**:
- Page header: "All Recycling Logs"
- Advanced filter panel (collapsible):
  - Date range (start, end)
  - Material types (multi-select checkboxes)
  - Route/Zone (dropdown)
  - Crew member (searchable dropdown)
  - Quality issues only (checkbox)
  - "Apply Filters" and "Clear Filters" buttons
- Action bar:
  - Export button (downloads CSV of filtered results)
  - Results count: "Showing X logs"
- Table with columns:
  - Collection Date
  - Crew Member
  - Route/Zone
  - Materials
  - Total Weight (kg)
  - Quality Issue (icon if flagged)
  - Actions (View Details)
- Pagination (50 logs per page)

**Visual Design**:
- Quality issue rows highlighted with amber background
- Export button with download icon
- Filter panel uses accordion for space efficiency

#### Analytics Dashboard (`/admin/recycling/analytics`)

**Layout**:
- Page header: "Recycling Analytics"
- Date range selector (global filter)
- Tab navigation:
  - Overview (default)
  - Materials
  - Zones
  - Trends
  - Crew Performance

**Overview Tab**:
- Key metrics cards (4 across):
  - Total Weight Collected
  - Total Logs
  - Average Weight per Log
  - Recycling Rate
- Charts:
  - Material breakdown (pie chart)
  - Weekly trend (line chart, last 12 weeks)
- Target progress section:
  - Progress bars for each active target
  - Green highlight if achieved, amber if >80%, gray otherwise

**Materials Tab**:
- Material totals table:
  - Material Type
  - Total Weight (kg)
  - Percentage of Total
  - Number of Logs
- Bar chart: Materials by weight (horizontal bars, sorted descending)
- Comparison with previous period (percentage change indicators)

**Zones Tab**:
- Zone performance table:
  - Zone/Route Name
  - Total Weight (kg)
  - Number of Logs
  - Average per Log
  - Highlight (if above average)
- Map visualization (optional for future enhancement)
- Filter by material type

**Trends Tab**:
- Interval selector: Daily / Weekly / Monthly
- Material type filter (multi-select)
- Line chart: Weight over time
- Percentage change indicators between intervals
- Data table below chart with detailed numbers

**Crew Performance Tab**:
- Crew ranking table:
  - Rank
  - Crew Member Name
  - Total Weight (kg)
  - Number of Logs
  - Average per Log
- Filter by date range
- Top performer badge (gold medal icon for #1)

#### Targets Management (`/admin/recycling/targets`)

**Layout**:
- Page header: "Recycling Targets"
- Current month targets section:
  - List of active targets with progress bars
  - Edit/Delete actions
- "Create New Target" form:
  - Month selector
  - Material type (dropdown with "All Materials" option)
  - Target weight (kg)
  - "Save Target" button

**Visual Design**:
- Progress bars color-coded (green if achieved, amber if close, gray otherwise)
- Achieved targets show checkmark icon
- Form validation for duplicate month+material combinations

### Responsive Design

**Mobile Considerations**:
- Tables convert to card layout on small screens
- Charts resize and simplify for mobile
- Filter panels become full-screen overlays
- Material entry form stacks vertically
- Touch-friendly button sizes (min 44px)

**Tablet Considerations**:
- Two-column layout for forms
- Side-by-side charts where appropriate
- Collapsible sidebar navigation

### Color Scheme (Consistent with SWEEP)

- **Primary (Forest Green)**: #2E8B57 - Main actions, headers
- **Secondary (Amber)**: #F4A300 - Warnings, quality issues
- **Accent (Teal)**: #4FB4A2 - Charts, highlights
- **Background**: #F9FAFB - Page background
- **Text**: #333333 - Primary text
- **Material Type Colors**:
  - Plastic: #3B82F6 (blue)
  - Paper: #92400E (brown)
  - Glass: #10B981 (green)
  - Metal: #6B7280 (gray)
  - Cardboard: #D97706 (tan)
  - Organic: #84CC16 (lime)

## Implementation Considerations

### Database Migrations

**Migration Order**:
1. `create_recycling_logs_table` - Main logs table
2. `create_recycling_log_materials_table` - Materials pivot table
3. `create_recycling_targets_table` - Targets table

**Indexes**:
- `recycling_logs`: index on `user_id`, `collection_date`, `route_id`, `assignment_id`
- `recycling_log_materials`: index on `recycling_log_id`, `material_type`
- `recycling_targets`: unique index on `month` + `material_type`

**Foreign Keys**:
- All foreign keys with `onDelete('cascade')` except user_id (restrict)
- Soft deletes on recycling_logs prevent actual deletion

### Performance Optimization

**Query Optimization**:
- Eager load relationships: `with(['user', 'route', 'materials'])`
- Use `select()` to limit columns in list views
- Index frequently filtered columns
- Pagination on all list views

**Caching Strategy**:
- Cache analytics data: 15-minute TTL
- Cache keys include filter parameters
- Invalidate on log creation/update
- Use Laravel's cache tags for grouped invalidation

**Database Optimization**:
- Consider materialized view for zone performance (future enhancement)
- Archive old logs (>2 years) to separate table (future enhancement)

### Security Considerations

**Authorization**:
- Policy class for RecyclingLog: `view`, `create`, `update`, `delete`
- Middleware on all routes: `auth` + role check
- Edit window enforced at policy level
- CSRF protection on all forms

**Input Validation**:
- Server-side validation on all inputs
- SQL injection prevention via Eloquent ORM
- XSS prevention via Blade escaping
- File upload not required (reduces attack surface)

**Data Privacy**:
- Crew members see only their own logs
- Administrators see all logs but with audit trail
- Soft deletes maintain data integrity
- No PII in recycling logs (user_id reference only)

### Scalability Considerations

**Current MVP Scope**:
- Expected load: <100 logs per day
- Single database server sufficient
- No queue system required

**Future Enhancements**:
- Queue CSV exports for large datasets
- Redis cache for high-traffic analytics
- Read replicas for reporting queries
- API endpoints for mobile app integration

### Deployment Considerations

**Environment Configuration**:
- Cache driver: file (MVP), redis (production)
- Queue driver: sync (MVP), database (production)
- Session driver: database (existing)

**Database Seeding**:
- Seed material types as constants (not database table)
- Seed sample recycling logs for testing
- Seed sample targets for demonstration

**Monitoring**:
- Log all recycling log creation/updates
- Monitor cache hit rates
- Track export generation times
- Alert on validation error spikes

### Accessibility Considerations

**WCAG 2.1 AA Compliance**:
- Semantic HTML structure
- ARIA labels on interactive elements
- Keyboard navigation support
- Color contrast ratios meet standards
- Form labels properly associated
- Error messages announced to screen readers
- Chart data available in table format

**Specific Implementations**:
- Material type badges include text, not just color
- Date pickers keyboard accessible
- Filter panels have clear focus indicators
- Export button has descriptive aria-label
- Charts have alt text descriptions

## Dependencies

### Existing SWEEP Components

- User model with Spatie Permission
- Route model with zone attribute
- Assignment model with date and route
- Bootstrap 5 UI framework
- Laravel Breeze authentication
- Blade templating engine

### New Package Requirements

- **Laravel Charts** (already in sysarch.md): For analytics visualizations
  - Pie charts for material breakdown
  - Line charts for trends
  - Bar charts for zone/crew performance

### No Additional Packages Required

- CSV export: Native Laravel response
- Date filtering: Native Carbon
- Caching: Native Laravel cache
- Validation: Native Laravel validation

## Migration Path

### Phase 1: Core Functionality (MVP)

- RecyclingLog and RecyclingLogMaterial models
- Crew CRUD operations
- Basic admin list view
- Simple material breakdown analytics

### Phase 2: Analytics Enhancement

- RecyclingAnalyticsService with caching
- Zone performance analytics
- Crew performance analytics
- Trend analysis with intervals

### Phase 3: Advanced Features

- RecyclingTarget model and management
- Target progress tracking
- Quality issue tracking and analytics
- CSV export functionality

### Phase 4: Polish and Optimization

- Chart visualizations
- Advanced filtering
- Performance optimization
- Accessibility improvements

**Design Rationale**: Phased approach allows for iterative development and early user feedback. Core functionality delivers immediate value while advanced features enhance usability.