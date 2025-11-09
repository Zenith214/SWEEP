# Dashboard Analytics Performance Optimization

This document describes the performance optimizations implemented for the Dashboard & Analytics feature.

## Database Indexes

### Added Indexes

The following indexes have been added to optimize query performance:

#### Collection Logs Table
- `idx_collection_logs_created_at` - Index on `created_at` for date range queries
- `idx_collection_logs_status_created` - Composite index on `status` and `created_at` for filtered date queries

#### Assignments Table
- `idx_assignments_date_crew` - Composite index on `assignment_date` and `user_id` for crew performance queries
- `idx_assignments_date_status` - Composite index on `assignment_date` and `status` for active assignment queries
- `idx_assignments_route_date` - Composite index on `route_id` and `assignment_date` for route performance queries

#### Reports Table
- `idx_reports_created_status` - Composite index on `created_at` and `status` for report statistics
- `idx_reports_route_created` - Composite index on `route_id` and `created_at` for route-specific reports
- `idx_reports_status_created` - Composite index on `status` and `created_at` for status filtering

#### Recycling Logs Table
- `idx_recycling_logs_created_at` - Index on `created_at` for date range queries
- `idx_recycling_logs_assignment_created` - Composite index on `assignment_id` and `created_at`

#### Trucks Table
- `idx_trucks_operational_status` - Index on `operational_status` for fleet metrics

#### Routes Table
- `idx_routes_is_active` - Index on `is_active` for active route filtering

### Migration

The indexes are created in migration: `2025_11_09_094450_add_performance_indexes_to_analytics_tables.php`

## Eager Loading Optimizations

### AnalyticsService Improvements

1. **Collection Metrics**: Added eager loading for `assignment.route` and `assignment.user` relationships
2. **Recycling Metrics**: Added eager loading for `materials` relationship
3. **Crew Performance**: Added eager loading for `creator` and `assignment` relationships
4. **Report Statistics**: Added eager loading for `resident` and `route` relationships
5. **Route Performance**: Added eager loading for `assignment.route` relationship

### Trend Data Generation Optimization

The `generateCollectionTrendData()` method was optimized to eliminate N+1 queries:

**Before**: Made separate queries for each day in the date range
**After**: Loads all assignments and collection logs upfront, then groups by date in memory

This reduces database queries from O(n) where n = days in range to just 2 queries total.

### Usage Statistics Optimization

The `getUsageStatistics()` method was optimized to use a direct join instead of `whereHas`:

**Before**: Used `whereHas` with subquery
**After**: Uses direct join with `distinct` count

This reduces query complexity and improves performance.

## Caching Strategy

### CacheConfigService

A centralized caching configuration service provides:

- **Consistent TTLs**: 
  - Realtime metrics: 5 minutes
  - Historical trends: 15 minutes
  - Static comparisons: 30 minutes
  - User preferences: No expiration

- **Cache Key Generation**: Standardized key generation with prefixes
- **Cache Tags**: Support for granular cache invalidation
- **Invalidation Methods**: Dedicated methods for each metric type

### Cache Invalidation

Model observers automatically invalidate relevant caches when data changes:

- **CollectionLogObserver**: Invalidates collection, crew, and route metrics
- **RecyclingLogObserver**: Invalidates recycling metrics
- **ReportObserver**: Invalidates report metrics
- **AssignmentObserver**: Invalidates collection, fleet, crew, and route metrics

### DashboardService Integration

The DashboardService now uses CacheConfigService for:
- Generating cache keys
- Determining appropriate TTLs
- Consistent caching across all dashboard metrics

## Query Performance Monitoring

The `MonitorsQueryPerformance` trait is used throughout to:
- Log slow queries (>2 seconds)
- Track query execution times
- Provide fallback to cached data on errors

## Testing

### Unit Tests

`CacheConfigServiceTest` verifies:
- Unique cache key generation
- Correct TTL values for different metric types
- Cache invalidation functionality

### Performance Testing

To test query performance with large datasets:

```bash
# Run with query logging enabled
php artisan tinker
DB::enableQueryLog();
$service = app(AnalyticsService::class);
$metrics = $service->getCollectionMetrics(now()->subDays(30), now());
dd(DB::getQueryLog());
```

## Best Practices

1. **Always use eager loading** when accessing relationships in loops
2. **Use cache tags** when available (Redis/Memcached) for granular invalidation
3. **Monitor slow queries** using the performance monitoring trait
4. **Test with realistic data volumes** to identify bottlenecks
5. **Invalidate caches** when underlying data changes

## Future Optimizations

Potential areas for further optimization:

1. **Database Views**: Create materialized views for complex aggregations
2. **Read Replicas**: Use read replicas for analytics queries
3. **Queue Processing**: Move heavy calculations to background jobs
4. **Partial Caching**: Cache individual metric components separately
5. **Redis Optimization**: Use Redis sorted sets for leaderboards and rankings
