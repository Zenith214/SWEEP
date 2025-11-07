# Dashboard & Analytics Design Document

## Overview

The Dashboard & Analytics feature provides a comprehensive data visualization and monitoring system for the SWEEP platform. It delivers role-specific dashboards that consolidate operational data from all modules (routes, assignments, collections, reports, recycling) into actionable insights. The design emphasizes real-time data updates, customizable views, and intuitive navigation to support data-driven decision-making across different user roles.

### Design Principles

- **Role-Based Views**: Each user role (Administrator, Collection Crew, Resident) receives a tailored dashboard experience
- **Performance First**: Efficient data aggregation and caching to ensure fast load times
- **Progressive Disclosure**: Summary metrics with drill-down capabilities for detailed analysis
- **Responsive Design**: Mobile-friendly layouts for crew members accessing dashboards in the field
- **Accessibility**: WCAG 2.1 AA compliant visualizations and interactions

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Admin      │  │    Crew      │  │   Resident   │      │
│  │  Dashboard   │  │  Dashboard   │  │  Dashboard   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Controller Layer                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         DashboardController                          │   │
│  │  - index()                                           │   │
│  │  - getMetrics()                                      │   │
│  │  - exportData()                                      │   │
│  │  - savePreferences()                                 │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                     Service Layer                            │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         DashboardService                             │   │
│  │  - getAdminMetrics()                                 │   │
│  │  - getCrewMetrics()                                  │   │
│  │  - getResidentMetrics()                              │   │
│  │  - calculateTrends()                                 │   │
│  │  - generateComparisons()                             │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         AnalyticsService                             │   │
│  │  - aggregateCollectionData()                         │   │
│  │  - calculateKPIs()                                   │   │
│  │  - generateChartData()                               │   │
│  └──────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         ExportService                                │   │
│  │  - exportToPDF()                                     │   │
│  │  - exportToCSV()                                     │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐        │
│  │ Routes  │  │Assignments│ │Collections│ │ Reports │        │
│  │  Model  │  │  Model   │  │  Model   │  │  Model  │        │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘        │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐                      │
│  │ Trucks  │  │  Users   │  │Dashboard│                      │
│  │  Model  │  │  Model   │  │Preferences│                    │
│  └─────────┘  └─────────┘  └─────────┘                      │
└─────────────────────────────────────────────────────────────┘
```

### Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade templates with Alpine.js for interactivity
- **Charts**: Chart.js for data visualization
- **Styling**: Tailwind CSS
- **Caching**: Redis for metric caching
- **Export**: DomPDF for PDF generation, Laravel Excel for CSV

### Design Rationale

**Why Laravel Service Layer**: Separating business logic into services (DashboardService, AnalyticsService) keeps controllers thin and promotes reusability across different dashboard views.

**Why Chart.js**: Lightweight, well-documented, and provides responsive charts that work well with Alpine.js for dynamic updates without full page reloads.

**Why Redis Caching**: Dashboard metrics involve complex aggregations across multiple tables. Caching results with short TTLs (5-15 minutes) significantly improves performance while maintaining data freshness.

## Components and Interfaces

### 1. Dashboard Controller

**Purpose**: Handle HTTP requests for dashboard views and AJAX metric updates

```php
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private ExportService $exportService
    ) {}
    
    // Display role-appropriate dashboard
    public function index(): View
    
    // Get fresh metrics via AJAX
    public function getMetrics(Request $request): JsonResponse
    
    // Export dashboard data
    public function export(Request $request): Response
    
    // Save user dashboard preferences
    public function savePreferences(Request $request): JsonResponse
    
    // Get drill-down data for specific metric
    public function drillDown(string $metric, Request $request): JsonResponse
}
```

### 2. Dashboard Service

**Purpose**: Orchestrate metric calculation and data aggregation for dashboards

```php
class DashboardService
{
    public function __construct(
        private AnalyticsService $analyticsService,
        private CacheManager $cache
    ) {}
    
    // Get all metrics for admin dashboard
    public function getAdminMetrics(array $filters = []): array
    
    // Get metrics for crew dashboard
    public function getCrewMetrics(User $crewMember): array
    
    // Get metrics for resident dashboard  
    public function getResidentMetrics(User $resident): array
    
    // Calculate trend indicators
    public function calculateTrends(string $metric, array $periods): array
    
    // Generate period comparisons
    public function generateComparisons(array $currentData, array $previousData): array
    
    // Get user dashboard preferences
    public function getUserPreferences(User $user): array
    
    // Save user dashboard preferences
    public function saveUserPreferences(User $user, array $preferences): bool
}
```

### 3. Analytics Service

**Purpose**: Perform data aggregation and KPI calculations

```php
class AnalyticsService
{
    // Collection performance metrics
    public function getCollectionMetrics(Carbon $startDate, Carbon $endDate, ?array $filters = null): array
    
    // Recycling performance metrics
    public function getRecyclingMetrics(Carbon $startDate, Carbon $endDate): array
    
    // Fleet utilization metrics
    public function getFleetMetrics(Carbon $startDate, Carbon $endDate): array
    
    // Crew performance metrics
    public function getCrewPerformance(Carbon $startDate, Carbon $endDate): array
    
    // Report statistics
    public function getReportStatistics(Carbon $startDate, Carbon $endDate): array
    
    // Route performance metrics
    public function getRoutePerformance(Carbon $startDate, Carbon $endDate): array
    
    // System usage statistics
    public function getUsageStatistics(Carbon $startDate, Carbon $endDate): array
    
    // Geographic distribution data
    public function getGeographicDistribution(Carbon $startDate, Carbon $endDate): array
    
    // Operational costs summary
    public function getOperationalCosts(Carbon $startDate, Carbon $endDate): array
    
    // Generate chart data in format ready for Chart.js
    public function generateChartData(string $chartType, array $rawData): array
}
```

### 4. Export Service

**Purpose**: Generate exportable reports in various formats

```php
class ExportService
{
    // Export dashboard to PDF
    public function exportToPDF(array $metrics, array $preferences): string
    
    // Export dashboard to CSV
    public function exportToCSV(array $metrics): string
    
    // Generate filename with timestamp
    private function generateFilename(string $format, Carbon $date): string
}
```

### 5. Dashboard Widgets (Frontend Components)

Each widget is a reusable Blade component with Alpine.js for interactivity:

- `<x-dashboard.metric-card />` - Display single KPI with comparison
- `<x-dashboard.chart-widget />` - Wrapper for Chart.js visualizations
- `<x-dashboard.alert-panel />` - Display notifications and alerts
- `<x-dashboard.data-table />` - Sortable data tables with drill-down
- `<x-dashboard.filter-bar />` - Date range and filter controls
- `<x-dashboard.export-button />` - Export functionality trigger

## Data Models

### Dashboard Preferences Model

**Purpose**: Store user-specific dashboard customizations

```php
class DashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_visibility',  // JSON: which widgets to show
        'widget_order',       // JSON: widget arrangement
        'default_filters',    // JSON: default date ranges, etc.
        'default_view',       // string: which dashboard view
    ];
    
    protected $casts = [
        'widget_visibility' => 'array',
        'widget_order' => 'array',
        'default_filters' => 'array',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Scheduled Report Model

**Purpose**: Store automated report configurations

```php
class ScheduledReport extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'frequency',          // daily, weekly, monthly
        'metrics',            // JSON: which metrics to include
        'format',             // pdf, csv
        'last_generated_at',
        'next_generation_at',
        'is_active',
    ];
    
    protected $casts = [
        'metrics' => 'array',
        'last_generated_at' => 'datetime',
        'next_generation_at' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function generatedReports(): HasMany
    {
        return $this->hasMany(GeneratedReport::class);
    }
}
```

### Generated Report Model

**Purpose**: Track generated report files

```php
class GeneratedReport extends Model
{
    protected $fillable = [
        'scheduled_report_id',
        'file_path',
        'generated_at',
        'period_start',
        'period_end',
    ];
    
    protected $casts = [
        'generated_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
    ];
    
    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class);
    }
}
```

### Metric Cache Strategy

**Design Decision**: Use Redis with structured keys and appropriate TTLs

```
Key Pattern: dashboard:{role}:{user_id}:{metric}:{period}:{filters_hash}
TTL Strategy:
- Real-time metrics (today's collections): 5 minutes
- Historical trends: 15 minutes  
- Static comparisons: 30 minutes
- User preferences: No expiration (invalidate on update)
```

## Error Handling

### Error Scenarios and Responses

1. **Missing Data**: Display "No data available" message in widget with option to adjust filters
2. **Calculation Errors**: Log error, display cached data if available, show generic error message to user
3. **Export Failures**: Return user-friendly error, log details for debugging
4. **Permission Errors**: Redirect to appropriate dashboard for user role
5. **Timeout on Complex Queries**: Implement query timeout, fall back to cached data, suggest narrower date range

### Logging Strategy

- Log all metric calculation errors with context (user, filters, query)
- Log export generation with file size and generation time
- Log slow queries (>2 seconds) for optimization
- Track dashboard load times for performance monitoring

## Testing Strategy

### Unit Tests

**DashboardService Tests**
- Test metric calculation for each user role
- Test trend calculation with various data patterns
- Test comparison generation with edge cases (no previous data, identical data)
- Test preference saving and retrieval
- Test caching behavior

**AnalyticsService Tests**
- Test each metric calculation method independently
- Test with empty datasets
- Test with edge dates (month boundaries, year boundaries)
- Test filtering logic
- Test chart data formatting

**ExportService Tests**
- Test PDF generation with various metric combinations
- Test CSV generation and formatting
- Test filename generation
- Test handling of large datasets

### Integration Tests

**Dashboard Display Tests**
- Test admin dashboard loads with all widgets
- Test crew dashboard shows correct assignments
- Test resident dashboard shows correct zone information
- Test role-based access control
- Test dashboard with various permission combinations

**Metric Calculation Tests**
- Test collection metrics aggregate correctly from database
- Test recycling calculations match expected formulas
- Test fleet utilization calculations
- Test crew performance rankings
- Test report statistics grouping

**Export Integration Tests**
- Test full export workflow (request → generation → download)
- Test exported data matches dashboard display
- Test export with custom date ranges
- Test concurrent export requests

### Feature Tests

**User Workflow Tests**
- Test complete admin workflow: login → view dashboard → drill down → export
- Test crew member workflow: login → view assignments → check performance
- Test resident workflow: login → check schedule → view reports
- Test dashboard customization: hide widget → save → reload → verify persistence
- Test scheduled report creation and generation

### Performance Tests

**Load Testing**
- Test dashboard load time with 1000+ routes
- Test dashboard load time with 10,000+ collections
- Test concurrent user access (50+ simultaneous users)
- Test export generation time with large datasets
- Test cache effectiveness under load

**Query Optimization**
- Identify N+1 queries in metric calculations
- Test database query performance with indexes
- Test aggregation query performance
- Monitor memory usage during complex calculations

## Implementation Phases

### Phase 1: Core Infrastructure
- Database migrations for preferences and scheduled reports
- Base controller and service structure
- Caching layer implementation
- Basic admin dashboard layout

### Phase 2: Admin Dashboard Metrics
- Collection status metrics (Req 2)
- Pending items metrics (Req 3)
- Collection performance trends (Req 4)
- Basic chart integration

### Phase 3: Extended Admin Metrics
- Recycling metrics (Req 5)
- Fleet utilization (Req 6)
- Crew performance (Req 7)
- Report statistics (Req 8)
- Route performance (Req 12)

### Phase 4: Customization & Export
- Dashboard customization (Req 9)
- Period comparisons (Req 10)
- Export functionality (Req 11)
- Drill-down navigation (Req 15)

### Phase 5: Advanced Features
- System usage statistics (Req 13)
- Alerts and notifications (Req 14)
- Geographic distribution (Req 18)
- Operational costs (Req 16)

### Phase 6: Scheduled Reports
- Scheduled report configuration (Req 17)
- Background job for report generation
- Report storage and retrieval

### Phase 7: Role-Specific Dashboards
- Crew dashboard (Req 19)
- Resident dashboard (Req 20)
- Role-based routing

## Security Considerations

### Authorization

- Implement policy-based authorization for dashboard access
- Ensure users can only see data relevant to their role and permissions
- Restrict export functionality to authorized roles
- Validate all filter inputs to prevent data leakage

### Data Privacy

- Anonymize crew performance data in exports if required by policy
- Restrict resident data visibility to authorized administrators
- Implement audit logging for sensitive metric access
- Ensure scheduled reports respect user permissions at generation time

### Input Validation

- Validate date ranges (prevent excessively large ranges)
- Sanitize all filter inputs
- Validate export format requests
- Validate widget visibility preferences

## Performance Optimization

### Database Optimization

**Indexes Required**:
```sql
-- Collections table
CREATE INDEX idx_collections_date_status ON collections(collection_date, status);
CREATE INDEX idx_collections_route_date ON collections(route_id, collection_date);

-- Assignments table  
CREATE INDEX idx_assignments_date_crew ON assignments(assignment_date, crew_member_id);

-- Reports table
CREATE INDEX idx_reports_created_status ON reports(created_at, status);
CREATE INDEX idx_reports_zone ON reports(zone_id, created_at);

-- Collection logs
CREATE INDEX idx_collection_logs_date ON collection_logs(logged_at);
```

**Query Optimization**:
- Use eager loading for relationships in metric calculations
- Implement database views for complex aggregations
- Use raw queries for performance-critical calculations
- Implement query result caching

### Caching Strategy

**Multi-Level Caching**:
1. **Application Cache** (Redis): Calculated metrics with 5-15 minute TTL
2. **Query Cache**: Database query results for common aggregations
3. **HTTP Cache**: Static dashboard assets with long TTL
4. **Browser Cache**: Chart.js library and dashboard JavaScript

**Cache Invalidation**:
- Invalidate collection metrics when new collections are logged
- Invalidate report metrics when reports are updated
- Invalidate user preferences immediately on save
- Use cache tags for granular invalidation

### Frontend Optimization

- Lazy load chart libraries
- Implement virtual scrolling for large data tables
- Use Alpine.js for reactive updates without full page reloads
- Compress and minify dashboard JavaScript
- Implement skeleton loaders for better perceived performance

## Accessibility

### WCAG 2.1 AA Compliance

**Visual Design**:
- Ensure 4.5:1 contrast ratio for all text
- Use color plus icons/patterns for chart differentiation (not color alone)
- Provide text alternatives for all charts
- Support browser zoom up to 200%

**Keyboard Navigation**:
- All interactive elements accessible via keyboard
- Logical tab order through dashboard widgets
- Keyboard shortcuts for common actions (refresh, export)
- Focus indicators clearly visible

**Screen Reader Support**:
- ARIA labels for all widgets and charts
- ARIA live regions for dynamic metric updates
- Descriptive link text for drill-down navigation
- Table headers properly associated with data cells

**Responsive Design**:
- Mobile-friendly layouts for crew dashboards
- Touch-friendly tap targets (minimum 44x44px)
- Horizontal scrolling for wide tables on mobile
- Collapsible widgets on smaller screens

## Monitoring and Maintenance

### Application Monitoring

- Track dashboard load times per role
- Monitor cache hit rates
- Track export generation times and failures
- Monitor scheduled report generation success rates
- Alert on slow metric calculations (>5 seconds)

### Data Quality Monitoring

- Validate metric calculations against known test data
- Monitor for anomalies in trend data
- Track missing or incomplete data scenarios
- Alert on significant metric deviations

### Maintenance Tasks

- Regular cache cleanup for expired entries
- Cleanup old generated reports (retain 90 days)
- Archive dashboard preference history
- Review and optimize slow queries monthly
- Update chart library and dependencies quarterly

