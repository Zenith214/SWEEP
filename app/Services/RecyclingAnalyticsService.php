<?php

namespace App\Services;

use App\Models\RecyclingLog;
use App\Models\RecyclingTarget;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecyclingAnalyticsService
{
    /**
     * Cache TTL in minutes.
     */
    protected const CACHE_TTL = 15;

    /**
     * Get material totals for a date range.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array
     */
    public function getMaterialTotals($startDate, $endDate): array
    {
        $cacheKey = $this->generateCacheKey('material_totals', $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($startDate, $endDate) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Get all materials within date range
            $materials = DB::table('recycling_log_materials')
                ->join('recycling_logs', 'recycling_log_materials.recycling_log_id', '=', 'recycling_logs.id')
                ->whereBetween('recycling_logs.collection_date', [$startDate, $endDate])
                ->whereNull('recycling_logs.deleted_at')
                ->select(
                    'recycling_log_materials.material_type',
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight'),
                    DB::raw('COUNT(DISTINCT recycling_logs.id) as log_count')
                )
                ->groupBy('recycling_log_materials.material_type')
                ->get();

            // Calculate total weight across all materials
            $totalWeight = $materials->sum('total_weight');

            // Build result array with percentages
            $result = $materials->map(function ($material) use ($totalWeight) {
                return [
                    'material_type' => $material->material_type,
                    'total_weight' => (float) $material->total_weight,
                    'log_count' => (int) $material->log_count,
                    'percentage' => $totalWeight > 0 
                        ? round(($material->total_weight / $totalWeight) * 100, 2) 
                        : 0,
                ];
            })
            ->sortByDesc('total_weight')
            ->values()
            ->toArray();

            return $result;
        });
    }

    /**
     * Get zone performance analysis.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @param array|null $materialTypes
     * @return array
     */
    public function getZonePerformance($startDate, $endDate, ?array $materialTypes = null): array
    {
        $cacheKey = $this->generateCacheKey('zone_performance', $startDate, $endDate, $materialTypes);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($startDate, $endDate, $materialTypes) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Build query
            $query = DB::table('recycling_logs')
                ->join('routes', 'recycling_logs.route_id', '=', 'routes.id')
                ->join('recycling_log_materials', 'recycling_logs.id', '=', 'recycling_log_materials.recycling_log_id')
                ->whereBetween('recycling_logs.collection_date', [$startDate, $endDate])
                ->whereNull('recycling_logs.deleted_at');

            // Filter by material types if specified
            if ($materialTypes && count($materialTypes) > 0) {
                $query->whereIn('recycling_log_materials.material_type', $materialTypes);
            }

            // Group by zone
            $zones = $query->select(
                    'routes.zone',
                    'routes.name as route_name',
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight'),
                    DB::raw('COUNT(DISTINCT recycling_logs.id) as log_count')
                )
                ->groupBy('routes.zone', 'routes.name')
                ->get();

            // Calculate average weight across all zones
            $averageWeight = $zones->count() > 0 
                ? $zones->sum('total_weight') / $zones->count() 
                : 0;

            // Build result with highlight flag
            $result = $zones->map(function ($zone) use ($averageWeight) {
                $totalWeight = (float) $zone->total_weight;
                $logCount = (int) $zone->log_count;

                return [
                    'zone' => $zone->zone,
                    'route_name' => $zone->route_name,
                    'total_weight' => $totalWeight,
                    'log_count' => $logCount,
                    'average_per_log' => $logCount > 0 ? round($totalWeight / $logCount, 2) : 0,
                    'highlight' => $totalWeight > $averageWeight,
                ];
            })
            ->sortByDesc('total_weight')
            ->values()
            ->toArray();

            return $result;
        });
    }

    /**
     * Get trend data aggregated by time intervals.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @param string $interval ('daily', 'weekly', 'monthly')
     * @param array|null $materialTypes
     * @return array
     */
    public function getTrendData($startDate, $endDate, string $interval = 'weekly', ?array $materialTypes = null): array
    {
        $cacheKey = $this->generateCacheKey('trend_data', $startDate, $endDate, $materialTypes, $interval);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($startDate, $endDate, $interval, $materialTypes) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Build query
            $query = DB::table('recycling_logs')
                ->join('recycling_log_materials', 'recycling_logs.id', '=', 'recycling_log_materials.recycling_log_id')
                ->whereBetween('recycling_logs.collection_date', [$startDate, $endDate])
                ->whereNull('recycling_logs.deleted_at');

            // Filter by material types if specified
            if ($materialTypes && count($materialTypes) > 0) {
                $query->whereIn('recycling_log_materials.material_type', $materialTypes);
            }

            // Determine date format based on interval
            $dateFormat = match ($interval) {
                'daily' => '%Y-%m-%d',
                'weekly' => '%Y-%u',  // Year-Week
                'monthly' => '%Y-%m',
                default => '%Y-%u',
            };

            // Group by interval
            $data = $query->select(
                    DB::raw("DATE_FORMAT(recycling_logs.collection_date, '{$dateFormat}') as period"),
                    DB::raw('MIN(recycling_logs.collection_date) as period_start'),
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight'),
                    DB::raw('COUNT(DISTINCT recycling_logs.id) as log_count')
                )
                ->groupBy('period')
                ->orderBy('period_start')
                ->get();

            // Calculate percentage changes
            $result = [];
            $previousWeight = null;

            foreach ($data as $index => $item) {
                $totalWeight = (float) $item->total_weight;
                $percentageChange = null;

                if ($previousWeight !== null && $previousWeight > 0) {
                    $percentageChange = round((($totalWeight - $previousWeight) / $previousWeight) * 100, 2);
                }

                $result[] = [
                    'period' => $item->period,
                    'period_start' => $item->period_start,
                    'period_label' => $this->formatPeriodLabel($item->period_start, $interval),
                    'total_weight' => $totalWeight,
                    'log_count' => (int) $item->log_count,
                    'percentage_change' => $percentageChange,
                ];

                $previousWeight = $totalWeight;
            }

            return $result;
        });
    }

    /**
     * Get crew performance statistics.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array
     */
    public function getCrewPerformance($startDate, $endDate): array
    {
        $cacheKey = $this->generateCacheKey('crew_performance', $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($startDate, $endDate) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Get crew performance data
            $crewData = DB::table('recycling_logs')
                ->join('users', 'recycling_logs.user_id', '=', 'users.id')
                ->join('recycling_log_materials', 'recycling_logs.id', '=', 'recycling_log_materials.recycling_log_id')
                ->whereBetween('recycling_logs.collection_date', [$startDate, $endDate])
                ->whereNull('recycling_logs.deleted_at')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight'),
                    DB::raw('COUNT(DISTINCT recycling_logs.id) as log_count')
                )
                ->groupBy('users.id', 'users.name')
                ->get();

            // Build result with average per log
            $result = $crewData->map(function ($crew) {
                $totalWeight = (float) $crew->total_weight;
                $logCount = (int) $crew->log_count;

                return [
                    'user_id' => $crew->user_id,
                    'user_name' => $crew->user_name,
                    'total_weight' => $totalWeight,
                    'log_count' => $logCount,
                    'average_per_log' => $logCount > 0 ? round($totalWeight / $logCount, 2) : 0,
                ];
            })
            ->sortByDesc('total_weight')
            ->values()
            ->toArray();

            return $result;
        });
    }

    /**
     * Get recycling rate metrics.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array
     */
    public function getRecyclingRate($startDate, $endDate): array
    {
        $cacheKey = $this->generateCacheKey('recycling_rate', $startDate, $endDate);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($startDate, $endDate) {
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Get total weight and counts
            $stats = DB::table('recycling_logs')
                ->join('recycling_log_materials', 'recycling_logs.id', '=', 'recycling_log_materials.recycling_log_id')
                ->whereBetween('recycling_logs.collection_date', [$startDate, $endDate])
                ->whereNull('recycling_logs.deleted_at')
                ->select(
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight'),
                    DB::raw('COUNT(DISTINCT recycling_logs.id) as log_count'),
                    DB::raw('COUNT(DISTINCT recycling_logs.route_id) as zone_count')
                )
                ->first();

            $totalWeight = (float) ($stats->total_weight ?? 0);
            $logCount = (int) ($stats->log_count ?? 0);
            $zoneCount = (int) ($stats->zone_count ?? 0);

            return [
                'total_weight' => $totalWeight,
                'log_count' => $logCount,
                'zone_count' => $zoneCount,
                'weight_per_log' => $logCount > 0 ? round($totalWeight / $logCount, 2) : 0,
                'weight_per_zone' => $zoneCount > 0 ? round($totalWeight / $zoneCount, 2) : 0,
            ];
        });
    }

    /**
     * Compare current period with previous period.
     *
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return array
     */
    public function compareWithPreviousPeriod($startDate, $endDate): array
    {
        $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
        $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

        // Calculate period length
        $periodLength = $startDate->diffInDays($endDate);

        // Calculate previous period dates
        $previousStart = $startDate->copy()->subDays($periodLength + 1);
        $previousEnd = $startDate->copy()->subDay();

        // Get current period stats
        $currentStats = $this->getRecyclingRate($startDate, $endDate);

        // Get previous period stats
        $previousStats = $this->getRecyclingRate($previousStart, $previousEnd);

        // Calculate percentage change
        $percentageChange = null;
        if ($previousStats['total_weight'] > 0) {
            $percentageChange = round(
                (($currentStats['total_weight'] - $previousStats['total_weight']) / $previousStats['total_weight']) * 100,
                2
            );
        }

        return [
            'current_period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_weight' => $currentStats['total_weight'],
            ],
            'previous_period' => [
                'start_date' => $previousStart->format('Y-m-d'),
                'end_date' => $previousEnd->format('Y-m-d'),
                'total_weight' => $previousStats['total_weight'],
            ],
            'percentage_change' => $percentageChange,
        ];
    }

    /**
     * Get target progress for a specific month.
     *
     * @param Carbon|string $month
     * @return array
     */
    public function getTargetProgress($month): array
    {
        $month = $month instanceof Carbon ? $month : Carbon::parse($month);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();

        $cacheKey = $this->generateCacheKey('target_progress', $monthStart, $monthEnd);

        return Cache::remember($cacheKey, self::CACHE_TTL * 60, function () use ($monthStart, $monthEnd) {
            // Get all targets for the month
            $targets = RecyclingTarget::where('month', $monthStart->format('Y-m-01'))->get();

            // Get actual weights for the month grouped by material type
            $actualWeights = DB::table('recycling_logs')
                ->join('recycling_log_materials', 'recycling_logs.id', '=', 'recycling_log_materials.recycling_log_id')
                ->whereBetween('recycling_logs.collection_date', [$monthStart, $monthEnd])
                ->whereNull('recycling_logs.deleted_at')
                ->select(
                    'recycling_log_materials.material_type',
                    DB::raw('SUM(recycling_log_materials.weight) as total_weight')
                )
                ->groupBy('recycling_log_materials.material_type')
                ->pluck('total_weight', 'material_type');

            // Calculate total weight across all materials
            $totalWeight = $actualWeights->sum();

            // Build result with progress for each target
            $result = $targets->map(function ($target) use ($actualWeights, $totalWeight) {
                // Determine actual weight for this target
                $actualWeight = 0;
                if ($target->material_type === null || $target->material_type === 'all') {
                    $actualWeight = $totalWeight;
                } else {
                    $actualWeight = $actualWeights->get($target->material_type, 0);
                }

                $targetWeight = (float) $target->target_weight;
                $progress = $targetWeight > 0 
                    ? round(($actualWeight / $targetWeight) * 100, 2) 
                    : 0;

                return [
                    'target_id' => $target->id,
                    'material_type' => $target->material_type,
                    'target_weight' => $targetWeight,
                    'actual_weight' => (float) $actualWeight,
                    'progress_percentage' => $progress,
                    'is_achieved' => $actualWeight >= $targetWeight,
                    'month' => $target->month,
                ];
            })
            ->toArray();

            return $result;
        });
    }

    /**
     * Invalidate all analytics caches.
     *
     * @return void
     */
    public function invalidateCache(): void
    {
        // Clear all caches with the recycling_analytics prefix
        Cache::flush();
    }

    /**
     * Generate a cache key for analytics data.
     *
     * @param string $type
     * @param mixed ...$params
     * @return string
     */
    protected function generateCacheKey(string $type, ...$params): string
    {
        $key = "recycling_analytics:{$type}";

        foreach ($params as $param) {
            if ($param instanceof Carbon) {
                $key .= ':' . $param->format('Y-m-d');
            } elseif (is_array($param)) {
                $key .= ':' . implode(',', $param);
            } elseif ($param !== null) {
                $key .= ':' . $param;
            }
        }

        return $key;
    }

    /**
     * Format period label based on interval type.
     *
     * @param string $date
     * @param string $interval
     * @return string
     */
    protected function formatPeriodLabel(string $date, string $interval): string
    {
        $carbon = Carbon::parse($date);

        return match ($interval) {
            'daily' => $carbon->format('M d, Y'),
            'weekly' => 'Week of ' . $carbon->startOfWeek()->format('M d, Y'),
            'monthly' => $carbon->format('F Y'),
            default => $carbon->format('M d, Y'),
        };
    }
}
