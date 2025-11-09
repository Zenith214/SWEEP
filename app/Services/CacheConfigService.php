<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheConfigService
{
    /**
     * Cache TTL configurations in seconds.
     */
    public const TTL_REALTIME = 300; // 5 minutes - for today's metrics
    public const TTL_HISTORICAL = 900; // 15 minutes - for historical trends
    public const TTL_STATIC = 1800; // 30 minutes - for comparisons
    public const TTL_PREFERENCES = null; // No expiration - invalidate on update

    /**
     * Cache key prefixes for different metric types.
     */
    public const PREFIX_COLLECTION = 'analytics:collection';
    public const PREFIX_RECYCLING = 'analytics:recycling';
    public const PREFIX_FLEET = 'analytics:fleet';
    public const PREFIX_CREW = 'analytics:crew';
    public const PREFIX_REPORTS = 'analytics:reports';
    public const PREFIX_ROUTES = 'analytics:routes';
    public const PREFIX_USAGE = 'analytics:usage';
    public const PREFIX_GEOGRAPHIC = 'analytics:geographic';
    public const PREFIX_COSTS = 'analytics:costs';
    public const PREFIX_DASHBOARD = 'dashboard';

    /**
     * Cache tags for granular invalidation.
     */
    public const TAG_COLLECTION_METRICS = 'collection_metrics';
    public const TAG_RECYCLING_METRICS = 'recycling_metrics';
    public const TAG_FLEET_METRICS = 'fleet_metrics';
    public const TAG_CREW_METRICS = 'crew_metrics';
    public const TAG_REPORT_METRICS = 'report_metrics';
    public const TAG_ROUTE_METRICS = 'route_metrics';
    public const TAG_USAGE_METRICS = 'usage_metrics';
    public const TAG_DASHBOARD_DATA = 'dashboard_data';

    /**
     * Generate a cache key with prefix and parameters.
     *
     * @param string $prefix
     * @param array $params
     * @return string
     */
    public static function generateKey(string $prefix, array $params): string
    {
        return $prefix . ':' . md5(json_encode($params));
    }

    /**
     * Get TTL based on metric type.
     *
     * @param string $metricType
     * @return int|null
     */
    public static function getTTL(string $metricType): ?int
    {
        return match ($metricType) {
            'realtime', 'today' => self::TTL_REALTIME,
            'historical', 'trends' => self::TTL_HISTORICAL,
            'comparison', 'static' => self::TTL_STATIC,
            'preferences' => self::TTL_PREFERENCES,
            default => self::TTL_HISTORICAL,
        };
    }

    /**
     * Invalidate cache by tags.
     *
     * @param array $tags
     * @return void
     */
    public static function invalidateByTags(array $tags): void
    {
        // Note: Cache tags require Redis or Memcached driver
        // For database cache, we'll use key patterns
        if (config('cache.default') === 'redis') {
            Cache::tags($tags)->flush();
        } else {
            // For non-taggable drivers, we'll need to track keys manually
            // This is a simplified approach
            foreach ($tags as $tag) {
                Cache::forget($tag);
            }
        }
    }

    /**
     * Invalidate all collection-related caches.
     *
     * @return void
     */
    public static function invalidateCollectionMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_COLLECTION_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all recycling-related caches.
     *
     * @return void
     */
    public static function invalidateRecyclingMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_RECYCLING_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all fleet-related caches.
     *
     * @return void
     */
    public static function invalidateFleetMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_FLEET_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all crew-related caches.
     *
     * @return void
     */
    public static function invalidateCrewMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_CREW_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all report-related caches.
     *
     * @return void
     */
    public static function invalidateReportMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_REPORT_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all route-related caches.
     *
     * @return void
     */
    public static function invalidateRouteMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_ROUTE_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }

    /**
     * Invalidate all dashboard caches.
     *
     * @return void
     */
    public static function invalidateAllDashboardMetrics(): void
    {
        self::invalidateByTags([
            self::TAG_COLLECTION_METRICS,
            self::TAG_RECYCLING_METRICS,
            self::TAG_FLEET_METRICS,
            self::TAG_CREW_METRICS,
            self::TAG_REPORT_METRICS,
            self::TAG_ROUTE_METRICS,
            self::TAG_USAGE_METRICS,
            self::TAG_DASHBOARD_DATA,
        ]);
    }
}
