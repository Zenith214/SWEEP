# Dashboard Error Handling and Logging

This document describes the comprehensive error handling and logging system implemented for the Dashboard & Analytics feature.

## Overview

The error handling system provides:
- Graceful degradation when data is unavailable
- Fallback to cached data on calculation errors
- User-friendly error messages for missing data scenarios
- Slow query logging (>2 seconds) for optimization
- Dashboard load time tracking for performance monitoring
- Timeout handling for complex queries

## Components

### 1. Performance Tracking Middleware

**File:** `app/Http/Middleware/TrackDashboardPerformance.php`

Automatically tracks dashboard load times and memory usage for all dashboard routes.

**Features:**
- Measures page load time in milliseconds
- Tracks memory usage in MB
- Logs slow dashboard loads (>2 seconds)
- Logs high memory usage (>50MB)
- Adds performance headers in non-production environments

**Usage:**
The middleware is automatically applied to all dashboard routes via the `DashboardController` constructor.

### 2. Query Performance Monitoring Trait

**File:** `app/Traits/MonitorsQueryPerformance.php`

Provides methods for monitoring and handling query performance across services.

**Key Methods:**

#### `executeMonitoredQuery(callable $callback, string $queryName, int $timeoutSeconds = 30)`
Executes a query with performance monitoring and timeout protection.

```php
$result = $this->executeMonitoredQuery(
    fn() => CollectionLog::forDateRange($start, $end)->get(),
    'collection_logs_query',
    30
);
```

#### `executeWithCacheFallback(callable $callback, string $cacheKey, string $queryName, mixed $defaultValue = [])`
Executes a query with automatic fallback to cached data on failure.

```php
$metrics = $this->executeWithCacheFallback(
    fn() => $this->calculateMetrics(),
    'metrics_cache_key',
    'metrics_calculation',
    []
);
```

#### `getUserFriendlyErrorMessage(\Exception $exception, string $context)`
Converts technical exceptions into user-friendly error messages.

### 3. Dashboard Error Helper

**File:** `app/Helpers/DashboardErrorHelper.php`

Provides utility methods for handling and displaying errors in dashboard views.

**Key Methods:**
- `hasError(array $metric)` - Check if a metric has an error
- `isMissingData(mixed $data)` - Check if data is missing or empty
- `getMissingDataMessage(string $context)` - Get user-friendly message for missing data
- `getSuggestions(string $context)` - Get suggestions for resolving missing data
- `formatErrorForDisplay(array $metric, string $context)` - Format error for dashboard display

### 4. Error Message Blade Component

**File:** `resources/views/components/dashboard/error-message.blade.php`

Reusable component for displaying errors and empty states in dashboard widgets.

**Usage:**
```blade
<x-dashboard.error-message :metric="$collectionMetrics" context="collection_metrics" />

@if(!DashboardErrorHelper::hasError($collectionMetrics) && !DashboardErrorHelper::isMissingData($collectionMetrics))
    <!-- Display normal widget content -->
@endif
```

## Error Handling Patterns

### Service Layer Error Handling

Services use try-catch blocks with fallback to cached data:

```php
public function getMetrics(Carbon $start, Carbon $end): array
{
    $cacheKey = 'metrics:' . md5(json_encode([$start, $end]));
    
    return $this->executeWithCacheFallback(
        function () use ($start, $end) {
            // Execute query with monitoring
            return $this->executeMonitoredQuery(
                fn() => $this->performCalculation($start, $end),
                'metrics_calculation',
                30
            );
        },
        $cacheKey,
        'metrics',
        $this->getEmptyMetrics($start, $end)
    );
}
```

### Controller Layer Error Handling

Controllers catch exceptions and provide user-friendly responses:

```php
try {
    $metrics = $this->dashboardService->getAdminMetrics($filters);
    return view('dashboards.admin', compact('metrics'));
} catch (\Exception $e) {
    Log::error('Dashboard load failed', ['error' => $e->getMessage()]);
    
    return redirect()
        ->route('dashboard')
        ->with('error', 'Unable to load dashboard. Please try again.');
}
```

### View Layer Error Handling

Views use the error helper and component to display errors gracefully:

```blade
@php
    use App\Helpers\DashboardErrorHelper;
@endphp

<div class="dashboard-widget">
    <h3>Collection Metrics</h3>
    
    @if(DashboardErrorHelper::hasError($metrics['collection_metrics']) || 
        DashboardErrorHelper::isMissingData($metrics['collection_metrics']))
        <x-dashboard.error-message 
            :metric="$metrics['collection_metrics']" 
            context="collection_metrics" 
        />
    @else
        <!-- Display widget content -->
    @endif
</div>
```

## Logging Strategy

### Log Levels

- **INFO**: Normal operations (dashboard loads, successful queries)
- **WARNING**: Slow queries, high memory usage, cache fallbacks
- **ERROR**: Query failures, exceptions, data loading errors

### Log Context

All logs include relevant context:

```php
Log::warning('Slow query detected', [
    'query_name' => 'collection_metrics',
    'execution_time_ms' => 2500,
    'threshold_ms' => 2000,
    'user_id' => auth()->id(),
    'filters' => $filters,
    'timestamp' => now()->toIso8601String(),
]);
```

### Performance Logs

Dashboard load times are automatically logged:

```
[INFO] Dashboard load completed
{
    "route": "dashboard.admin",
    "load_time_ms": 1250,
    "memory_used_mb": 12.5,
    "user_id": 1,
    "user_role": "administrator",
    "filters": {"period": "30days"},
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Slow Query Logs

Queries exceeding 2 seconds are logged:

```
[WARNING] Slow query detected
{
    "query_name": "collection_metrics_query",
    "execution_time_ms": 3200,
    "threshold_ms": 2000,
    "timestamp": "2024-01-15T10:30:00Z"
}
```

## Timeout Handling

### Query Timeouts

Queries have configurable timeouts (default: 30 seconds):

```php
$result = $this->executeMonitoredQuery(
    fn() => $complexQuery(),
    'complex_query',
    60 // 60 second timeout
);
```

### Timeout Detection

The system detects timeout exceptions and provides appropriate messages:

```php
if ($this->isTimeoutException($exception)) {
    return "The request took too long to process. Please try narrowing your date range or filters.";
}
```

## Cache Fallback Strategy

### Primary Cache

Normal caching with TTL (5-15 minutes):
```php
Cache::remember($cacheKey, self::CACHE_TTL_REALTIME, fn() => $metrics);
```

### Fallback Cache

Long-term cache (24 hours) for error recovery:
```php
cache()->put($cacheKey . ':fallback', $result, now()->addHours(24));
```

### Fallback Flow

1. Try to execute query
2. On success, cache result for fallback
3. On failure, attempt to retrieve fallback cache
4. If no cache available, return default empty structure

## Maintenance Commands

### Cleanup Performance Logs

Remove old log files to free disk space:

```bash
php artisan dashboard:cleanup-logs --days=30
```

### Cleanup Export Files

Remove old export files:

```bash
php artisan dashboard:cleanup-exports --days=7
```

## Monitoring and Alerts

### Key Metrics to Monitor

1. **Dashboard Load Times**
   - Average load time
   - 95th percentile load time
   - Percentage of slow loads (>2s)

2. **Query Performance**
   - Slow query frequency
   - Query timeout frequency
   - Cache hit rate

3. **Error Rates**
   - Percentage of failed metric calculations
   - Cache fallback frequency
   - User-facing errors

### Recommended Alerts

- Alert when >10% of dashboard loads exceed 2 seconds
- Alert when query timeout rate exceeds 1%
- Alert when cache fallback rate exceeds 5%
- Alert when error rate exceeds 2%

## Best Practices

### For Developers

1. **Always use monitored queries** for database operations in services
2. **Implement cache fallback** for all metric calculations
3. **Provide empty data structures** as defaults
4. **Log with context** - include relevant information for debugging
5. **Test error scenarios** - verify graceful degradation

### For Administrators

1. **Monitor log files** regularly for slow queries and errors
2. **Review performance metrics** weekly
3. **Optimize slow queries** identified in logs
4. **Adjust cache TTLs** based on data freshness requirements
5. **Clean up old logs** monthly to manage disk space

## Troubleshooting

### Dashboard Loads Slowly

1. Check logs for slow queries
2. Review query execution plans
3. Add database indexes as needed
4. Increase cache TTL if appropriate
5. Consider narrowing default date ranges

### Frequent Cache Fallbacks

1. Investigate underlying query failures
2. Check database connection stability
3. Review query timeouts
4. Verify data integrity

### High Memory Usage

1. Review query result sizes
2. Implement pagination for large datasets
3. Optimize eager loading
4. Consider chunking large operations

## Configuration

### Environment Variables

```env
# Cache TTL (seconds)
DASHBOARD_CACHE_TTL_REALTIME=300
DASHBOARD_CACHE_TTL_HISTORICAL=900
DASHBOARD_CACHE_TTL_STATIC=1800

# Query timeout (seconds)
DASHBOARD_QUERY_TIMEOUT=30

# Performance thresholds
DASHBOARD_SLOW_LOAD_THRESHOLD=2000  # milliseconds
DASHBOARD_SLOW_QUERY_THRESHOLD=2000 # milliseconds
DASHBOARD_HIGH_MEMORY_THRESHOLD=50  # MB
```

### Customization

Thresholds can be customized in:
- `app/Http/Middleware/TrackDashboardPerformance.php`
- `app/Traits/MonitorsQueryPerformance.php`

## Testing

### Unit Tests

Test error handling in services:

```php
public function test_handles_query_failure_gracefully()
{
    // Mock database failure
    DB::shouldReceive('select')->andThrow(new \Exception('Connection failed'));
    
    $result = $this->service->getMetrics($start, $end);
    
    // Should return empty structure, not throw exception
    $this->assertIsArray($result);
    $this->assertEquals(0, $result['total_collections']);
}
```

### Integration Tests

Test full error flow:

```php
public function test_dashboard_displays_error_message_on_failure()
{
    // Simulate service failure
    $this->mock(DashboardService::class)
        ->shouldReceive('getAdminMetrics')
        ->andReturn(['collection_metrics' => ['error' => 'Failed to load']]);
    
    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    
    $response->assertSee('Unable to Load Data');
}
```

## Future Enhancements

1. **Real-time monitoring dashboard** for performance metrics
2. **Automated performance regression detection**
3. **Query optimization suggestions** based on slow query patterns
4. **Predictive caching** based on usage patterns
5. **Distributed tracing** for complex query chains
