<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\RecyclingLog;
use App\Models\Report;
use App\Models\Route;
use App\Models\Truck;
use App\Models\User;
use App\Traits\MonitorsQueryPerformance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    use MonitorsQueryPerformance;
    /**
     * Get collection metrics for a date range.
     * Calculates daily collection status and completion rates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array|null $filters Optional filters (route_id, zone, user_id)
     * @return array
     */
    public function getCollectionMetrics(Carbon $startDate, Carbon $endDate, ?array $filters = null): array
    {
        $cacheKey = 'analytics:collection_metrics:' . md5(json_encode([$startDate, $endDate, $filters]));
        
        return $this->executeWithCacheFallback(
            function () use ($startDate, $endDate, $filters, $cacheKey) {
                $query = CollectionLog::forDateRange($startDate, $endDate)
                    ->with(['assignment.route', 'assignment.user']);

                // Apply filters
                if ($filters) {
                    if (isset($filters['route_id'])) {
                        $query->whereHas('assignment', fn($q) => $q->where('route_id', $filters['route_id']));
                    }
                    if (isset($filters['zone'])) {
                        $query->whereHas('assignment.route', fn($q) => $q->where('zone', $filters['zone']));
                    }
                    if (isset($filters['user_id'])) {
                        $query->where('created_by', $filters['user_id']);
                    }
                }

                $logs = $this->executeMonitoredQuery(
                    fn() => $query->get(),
                    'collection_metrics_query',
                    30
                );

            $totalCollections = $logs->count();
            $completedCollections = $logs->where('status', CollectionLog::STATUS_COMPLETED)->count();
            $incompleteCollections = $logs->where('status', CollectionLog::STATUS_INCOMPLETE)->count();
            $issueCollections = $logs->where('status', CollectionLog::STATUS_ISSUE_REPORTED)->count();

            $completionRate = $totalCollections > 0 
                ? round(($completedCollections / $totalCollections) * 100, 2) 
                : 0;

            // Get today's specific metrics
            $todayScheduled = Assignment::active()
                ->forDate(now())
                ->count();
            
            $todayCompleted = CollectionLog::whereHas('assignment', function ($q) {
                    $q->forDate(now());
                })
                ->where('status', CollectionLog::STATUS_COMPLETED)
                ->count();
            
            $todayIssues = CollectionLog::whereHas('assignment', function ($q) {
                    $q->forDate(now());
                })
                ->where('status', CollectionLog::STATUS_ISSUE_REPORTED)
                ->count();
            
            $todayCompletionRate = $todayScheduled > 0 
                ? round(($todayCompleted / $todayScheduled) * 100, 2) 
                : 0;

            // Calculate yesterday's completion rate for comparison
            $yesterdayScheduled = Assignment::active()
                ->forDate(now()->subDay())
                ->count();
            
            $yesterdayCompleted = CollectionLog::whereHas('assignment', function ($q) {
                    $q->forDate(now()->subDay());
                })
                ->where('status', CollectionLog::STATUS_COMPLETED)
                ->count();
            
            $yesterdayCompletionRate = $yesterdayScheduled > 0 
                ? round(($yesterdayCompleted / $yesterdayScheduled) * 100, 2) 
                : 0;
            
            $completionRateChange = $todayCompletionRate - $yesterdayCompletionRate;
            $completionTrend = abs($completionRateChange) < 5 ? 'stable' : ($completionRateChange > 0 ? 'increasing' : 'decreasing');

            // Generate trend data for chart
            $trendData = $this->generateCollectionTrendData($startDate, $endDate, $filters);

                return [
                    'total_collections' => $totalCollections,
                    'completed' => $completedCollections,
                    'incomplete' => $incompleteCollections,
                    'issues_reported' => $issueCollections,
                    'completion_rate' => $completionRate,
                    'scheduled_today' => $todayScheduled,
                    'completed_today' => $todayCompleted,
                    'issues_today' => $todayIssues,
                    'completion_rate_today' => $todayCompletionRate,
                    'completion_rate_change' => round($completionRateChange, 1),
                    'completion_trend' => $completionTrend,
                    'trend_data' => $trendData,
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                ];
            },
            $cacheKey,
            'collection_metrics',
            $this->getEmptyCollectionMetrics($startDate, $endDate)
        );
    }

    /**
     * Generate collection trend data for charts.
     * Creates daily completion rate data for the specified period.
     * Optimized to reduce N+1 queries by loading all data upfront.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array|null $filters
     * @return array
     */
    private function generateCollectionTrendData(Carbon $startDate, Carbon $endDate, ?array $filters = null): array
    {
        try {
            // Load all assignments for the period at once
            $assignmentsQuery = Assignment::active()
                ->whereBetween('assignment_date', [$startDate, $endDate])
                ->select('id', 'assignment_date', 'route_id');

            if ($filters && isset($filters['route_id'])) {
                $assignmentsQuery->where('route_id', $filters['route_id']);
            }

            if ($filters && isset($filters['zone'])) {
                $assignmentsQuery->whereHas('route', fn($q) => $q->where('zone', $filters['zone']));
            }

            $assignments = $assignmentsQuery->get();
            $assignmentsByDate = $assignments->groupBy(fn($a) => $a->assignment_date->format('Y-m-d'));

            // Load all collection logs for the period at once
            $logsQuery = CollectionLog::whereHas('assignment', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('assignment_date', [$startDate, $endDate]);
            })
            ->where('status', CollectionLog::STATUS_COMPLETED)
            ->with('assignment:id,assignment_date,route_id');

            if ($filters && isset($filters['route_id'])) {
                $logsQuery->whereHas('assignment', fn($q) => $q->where('route_id', $filters['route_id']));
            }

            if ($filters && isset($filters['zone'])) {
                $logsQuery->whereHas('assignment.route', fn($q) => $q->where('zone', $filters['zone']));
            }

            $logs = $logsQuery->get();
            $logsByDate = $logs->groupBy(fn($l) => $l->assignment->assignment_date->format('Y-m-d'));

            // Generate trend data
            $period = CarbonPeriod::create($startDate, $endDate);
            $labels = [];
            $values = [];

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $scheduled = $assignmentsByDate->get($dateKey)?->count() ?? 0;
                $completed = $logsByDate->get($dateKey)?->count() ?? 0;

                $completionRate = $scheduled > 0 
                    ? round(($completed / $scheduled) * 100, 2) 
                    : 0;

                $labels[] = $date->format('M d');
                $values[] = $completionRate;
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        } catch (\Exception $e) {
            Log::error('Error generating collection trend data', [
                'error' => $e->getMessage(),
            ]);

            return [
                'labels' => [],
                'values' => [],
            ];
        }
    }

    /**
     * Get recycling metrics for a date range.
     * Aggregates recycling data by material type and calculates rates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getRecyclingMetrics(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $recyclingLogs = RecyclingLog::forDateRange($startDate, $endDate)
                ->with('materials')
                ->get();

            $totalWeight = 0;
            $materialBreakdown = [];

            foreach ($recyclingLogs as $log) {
                foreach ($log->materials as $material) {
                    $type = $material->material_type;
                    $weight = (float) $material->weight;
                    
                    if (!isset($materialBreakdown[$type])) {
                        $materialBreakdown[$type] = 0;
                    }
                    $materialBreakdown[$type] += $weight;
                    $totalWeight += $weight;
                }
            }

            // Calculate percentages
            $materialBreakdownWithPercentages = [];
            foreach ($materialBreakdown as $type => $weight) {
                $materialBreakdownWithPercentages[] = [
                    'material_type' => $type,
                    'weight' => round($weight, 2),
                    'percentage' => $totalWeight > 0 ? round(($weight / $totalWeight) * 100, 2) : 0,
                ];
            }

            // Sort by weight descending
            usort($materialBreakdownWithPercentages, fn($a, $b) => $b['weight'] <=> $a['weight']);

            // Calculate recycling rate (if we have collection data)
            $collectionCount = CollectionLog::forDateRange($startDate, $endDate)->count();
            $recyclingRate = $collectionCount > 0 
                ? round(($recyclingLogs->count() / $collectionCount) * 100, 2) 
                : 0;

            return [
                'total_weight' => round($totalWeight, 2),
                'total_logs' => $recyclingLogs->count(),
                'material_breakdown' => $materialBreakdownWithPercentages,
                'recycling_rate' => $recyclingRate,
                'logs_with_quality_issues' => $recyclingLogs->where('quality_issue', true)->count(),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating recycling metrics', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyRecyclingMetrics($startDate, $endDate);
        }
    }

    /**
     * Get fleet utilization metrics.
     * Calculates truck utilization and operational status.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getFleetMetrics(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $trucks = Truck::withTrashed()->get();
            $operationalTrucks = $trucks->where('operational_status', Truck::STATUS_OPERATIONAL);
            $maintenanceTrucks = $trucks->where('operational_status', Truck::STATUS_MAINTENANCE);
            $outOfServiceTrucks = $trucks->where('operational_status', Truck::STATUS_OUT_OF_SERVICE);

            $totalTrucks = $trucks->count();
            $operationalCount = $operationalTrucks->count();

            // Calculate utilization for operational trucks
            $utilizationData = [];
            $totalUtilization = 0;
            $underutilizedTrucks = [];

            foreach ($operationalTrucks as $truck) {
                $utilization = $truck->getUtilizationRate($startDate, $endDate);
                $utilizationData[] = [
                    'truck_id' => $truck->id,
                    'truck_number' => $truck->truck_number,
                    'utilization_rate' => $utilization,
                ];
                $totalUtilization += $utilization;

                // Trucks with less than 50% utilization are underutilized
                if ($utilization < 50) {
                    $underutilizedTrucks[] = [
                        'truck_id' => $truck->id,
                        'truck_number' => $truck->truck_number,
                        'utilization_rate' => $utilization,
                    ];
                }
            }

            $avgUtilization = $operationalCount > 0 
                ? round($totalUtilization / $operationalCount, 2) 
                : 0;

            // Get assignments in the period
            $assignmentsCount = Assignment::active()
                ->whereBetween('assignment_date', [$startDate, $endDate])
                ->distinct('truck_id')
                ->count('truck_id');

            $trucksWithAssignments = Assignment::active()
                ->whereBetween('assignment_date', [$startDate, $endDate])
                ->distinct('truck_id')
                ->count('truck_id');

            return [
                'total_trucks' => $totalTrucks,
                'operational' => $operationalCount,
                'maintenance' => $maintenanceTrucks->count(),
                'out_of_service' => $outOfServiceTrucks->count(),
                'average_utilization' => $avgUtilization,
                'trucks_with_assignments' => $trucksWithAssignments,
                'underutilized_trucks' => $underutilizedTrucks,
                'utilization_by_truck' => $utilizationData,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating fleet metrics', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyFleetMetrics($startDate, $endDate);
        }
    }

    /**
     * Get crew performance metrics.
     * Calculates crew member statistics and rankings.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getCrewPerformance(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $logs = CollectionLog::forDateRange($startDate, $endDate)
                ->with(['creator', 'assignment'])
                ->get();

            // Get active crew members with eager loading
            $crewMembers = User::role('collection_crew')
                ->select('id', 'name', 'email')
                ->get();
            $activeCrewCount = $crewMembers->count();

            // Group by crew member
            $crewPerformance = $logs->groupBy('created_by')
                ->map(function ($crewLogs, $userId) {
                    $totalLogs = $crewLogs->count();
                    $completedLogs = $crewLogs->where('status', CollectionLog::STATUS_COMPLETED)->count();
                    $incompleteLogs = $crewLogs->where('status', CollectionLog::STATUS_INCOMPLETE)->count();
                    $issueLogs = $crewLogs->where('status', CollectionLog::STATUS_ISSUE_REPORTED)->count();
                    
                    $completionRate = $totalLogs > 0 
                        ? round(($completedLogs / $totalLogs) * 100, 2) 
                        : 0;

                    // Calculate average completion time for completed logs
                    $completedWithTime = $crewLogs->filter(function ($log) {
                        return $log->status === CollectionLog::STATUS_COMPLETED && $log->completion_time;
                    });

                    $avgCompletionTime = null;
                    if ($completedWithTime->count() > 0) {
                        $totalMinutes = $completedWithTime->sum(function ($log) {
                            return $log->assignment->assignment_date->diffInMinutes($log->completion_time);
                        });
                        $avgCompletionTime = round($totalMinutes / $completedWithTime->count(), 2);
                    }

                    return [
                        'user_id' => $userId,
                        'user_name' => $crewLogs->first()->creator->name ?? 'Unknown',
                        'total_collections' => $totalLogs,
                        'completed' => $completedLogs,
                        'incomplete' => $incompleteLogs,
                        'issues_reported' => $issueLogs,
                        'completion_rate' => $completionRate,
                        'avg_completion_time_minutes' => $avgCompletionTime,
                    ];
                })
                ->sortByDesc('completion_rate')
                ->values()
                ->toArray();

            // Calculate average collections per crew member
            $totalCollections = $logs->count();
            $avgCollectionsPerCrew = $activeCrewCount > 0 
                ? round($totalCollections / $activeCrewCount, 2) 
                : 0;

            // Get top performers (top 5 by completion rate)
            $topPerformers = collect($crewPerformance)
                ->sortByDesc('completion_rate')
                ->take(5)
                ->values()
                ->toArray();

            // Get crew with most issues (top 5)
            $crewWithMostIssues = collect($crewPerformance)
                ->sortByDesc('issues_reported')
                ->take(5)
                ->values()
                ->toArray();

            return [
                'active_crew_count' => $activeCrewCount,
                'total_collections' => $totalCollections,
                'avg_collections_per_crew' => $avgCollectionsPerCrew,
                'top_performers' => $topPerformers,
                'crew_with_most_issues' => $crewWithMostIssues,
                'all_crew_performance' => $crewPerformance,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating crew performance', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyCrewPerformance($startDate, $endDate);
        }
    }

    /**
     * Get report statistics.
     * Aggregates report data by status and type.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getReportStatistics(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $reports = Report::forDateRange($startDate, $endDate)
                ->with(['resident', 'route'])
                ->get();

            $totalReports = $reports->count();

            // Group by status
            $byStatus = [
                'pending' => $reports->where('status', Report::STATUS_PENDING)->count(),
                'in_progress' => $reports->where('status', Report::STATUS_IN_PROGRESS)->count(),
                'resolved' => $reports->where('status', Report::STATUS_RESOLVED)->count(),
                'closed' => $reports->where('status', Report::STATUS_CLOSED)->count(),
            ];

            // Group by type
            $byType = [];
            foreach (Report::REPORT_TYPES as $typeKey => $typeLabel) {
                $byType[$typeKey] = [
                    'label' => $typeLabel,
                    'count' => $reports->where('report_type', $typeKey)->count(),
                ];
            }

            // Sort by count descending
            uasort($byType, fn($a, $b) => $b['count'] <=> $a['count']);

            // Calculate average resolution time
            $resolvedReports = $reports->filter(fn($r) => $r->isResolved() && $r->resolved_at);
            $avgResolutionTime = null;
            
            if ($resolvedReports->count() > 0) {
                $totalHours = $resolvedReports->sum(fn($r) => $r->getResolutionTime());
                $avgResolutionTime = round($totalHours / $resolvedReports->count(), 2);
            }

            // Get locations with most reports (by zone if available, otherwise by location string)
            $locationStats = [];
            if ($reports->first() && isset($reports->first()->zone)) {
                $locationStats = $reports->groupBy('zone')
                    ->map(fn($zoneReports, $zone) => [
                        'location' => $zone ?? 'Unknown',
                        'count' => $zoneReports->count(),
                    ])
                    ->sortByDesc('count')
                    ->take(5)
                    ->values()
                    ->toArray();
            }

            // Get most common report type
            $mostCommonType = collect($byType)
                ->sortByDesc('count')
                ->first();

            return [
                'total_reports' => $totalReports,
                'by_status' => $byStatus,
                'by_type' => $byType,
                'most_common_type' => $mostCommonType,
                'avg_resolution_time_hours' => $avgResolutionTime,
                'locations_with_most_reports' => $locationStats,
                'resolved_count' => $resolvedReports->count(),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating report statistics', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyReportStatistics($startDate, $endDate);
        }
    }

    /**
     * Get route performance metrics.
     * Calculates route-level completion rates and issues.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getRoutePerformance(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $logs = CollectionLog::forDateRange($startDate, $endDate)
                ->with(['assignment.route'])
                ->get();

            // Group by route
            $routePerformance = $logs->groupBy('assignment.route_id')
                ->map(function ($routeLogs, $routeId) {
                    $totalLogs = $routeLogs->count();
                    $completedLogs = $routeLogs->where('status', CollectionLog::STATUS_COMPLETED)->count();
                    $incompleteLogs = $routeLogs->where('status', CollectionLog::STATUS_INCOMPLETE)->count();
                    $issueLogs = $routeLogs->where('status', CollectionLog::STATUS_ISSUE_REPORTED)->count();
                    
                    $completionRate = $totalLogs > 0 
                        ? round(($completedLogs / $totalLogs) * 100, 2) 
                        : 0;

                    // Calculate average completion time for completed logs
                    $completedWithTime = $routeLogs->filter(function ($log) {
                        return $log->status === CollectionLog::STATUS_COMPLETED && $log->completion_time;
                    });

                    $avgCompletionTime = null;
                    if ($completedWithTime->count() > 0) {
                        $totalMinutes = $completedWithTime->sum(function ($log) {
                            return $log->assignment->assignment_date->diffInMinutes($log->completion_time);
                        });
                        $avgCompletionTime = round($totalMinutes / $completedWithTime->count(), 2);
                    }

                    $route = $routeLogs->first()->assignment->route;

                    return [
                        'route_id' => $routeId,
                        'route_name' => $route->name ?? 'Unknown',
                        'zone' => $route->zone ?? 'Unknown',
                        'total_collections' => $totalLogs,
                        'completed' => $completedLogs,
                        'incomplete' => $incompleteLogs,
                        'issues_reported' => $issueLogs,
                        'completion_rate' => $completionRate,
                        'avg_completion_time_minutes' => $avgCompletionTime,
                    ];
                })
                ->sortByDesc('completion_rate')
                ->values()
                ->toArray();

            // Get routes with lowest completion rates (bottom 5)
            $lowestCompletionRoutes = collect($routePerformance)
                ->sortBy('completion_rate')
                ->take(5)
                ->values()
                ->toArray();

            // Get routes with most issues (top 5)
            $routesWithMostIssues = collect($routePerformance)
                ->sortByDesc('issues_reported')
                ->take(5)
                ->values()
                ->toArray();

            return [
                'all_routes' => $routePerformance,
                'routes_with_lowest_completion' => $lowestCompletionRoutes,
                'routes_with_most_issues' => $routesWithMostIssues,
                'total_routes_tracked' => count($routePerformance),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating route performance', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyRoutePerformance($startDate, $endDate);
        }
    }

    /**
     * Get system usage statistics.
     * Tracks user activity and engagement.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getUsageStatistics(Carbon $startDate, Carbon $endDate): array
    {
        try {
            // Get active users by role
            $administrators = User::role('administrator')->count();
            $crewMembers = User::role('collection_crew')->count();
            $residents = User::role('resident')->count();

            // Get new resident registrations in the period
            $newResidents = User::role('resident')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Get reports submitted per active resident (optimized with join)
            $activeResidents = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->join('reports', 'users.id', '=', 'reports.resident_id')
                ->where('roles.name', 'resident')
                ->where('model_has_roles.model_type', User::class)
                ->whereBetween('reports.created_at', [$startDate, $endDate])
                ->distinct('users.id')
                ->count('users.id');

            $totalReports = Report::forDateRange($startDate, $endDate)->count();
            $reportsPerActiveResident = $activeResidents > 0 
                ? round($totalReports / $activeResidents, 2) 
                : 0;

            // Identify inactive users (no login in 30+ days)
            $inactiveThreshold = now()->subDays(30);
            $inactiveUsers = User::where('last_login_at', '<', $inactiveThreshold)
                ->orWhereNull('last_login_at')
                ->count();

            // Login activity trends (if last_login_at is tracked)
            $recentLogins = User::whereBetween('last_login_at', [$startDate, $endDate])
                ->count();

            return [
                'active_users_by_role' => [
                    'administrators' => $administrators,
                    'crew_members' => $crewMembers,
                    'residents' => $residents,
                    'total' => $administrators + $crewMembers + $residents,
                ],
                'new_resident_registrations' => $newResidents,
                'active_residents' => $activeResidents,
                'reports_per_active_resident' => $reportsPerActiveResident,
                'inactive_users_30_days' => $inactiveUsers,
                'recent_logins' => $recentLogins,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating usage statistics', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyUsageStatistics($startDate, $endDate);
        }
    }

    /**
     * Get geographic distribution of activities.
     * Aggregates data by zone.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getGeographicDistribution(Carbon $startDate, Carbon $endDate): array
    {
        try {
            // Get collection activity by zone
            $collectionsByZone = CollectionLog::forDateRange($startDate, $endDate)
                ->with('assignment.route')
                ->get()
                ->groupBy(fn($log) => $log->assignment->route->zone ?? 'Unknown')
                ->map(function ($zoneLogs, $zone) {
                    $completed = $zoneLogs->where('status', CollectionLog::STATUS_COMPLETED)->count();
                    $total = $zoneLogs->count();
                    
                    return [
                        'zone' => $zone,
                        'total_collections' => $total,
                        'completed_collections' => $completed,
                        'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                        'issues_reported' => $zoneLogs->where('status', CollectionLog::STATUS_ISSUE_REPORTED)->count(),
                    ];
                })
                ->sortByDesc('total_collections')
                ->values()
                ->toArray();

            // Get report activity by zone
            $reportsByZone = Report::forDateRange($startDate, $endDate)
                ->get()
                ->groupBy(fn($report) => $report->zone ?? 'Unknown')
                ->map(fn($zoneReports, $zone) => [
                    'zone' => $zone,
                    'total_reports' => $zoneReports->count(),
                    'pending_reports' => $zoneReports->where('status', Report::STATUS_PENDING)->count(),
                ])
                ->sortByDesc('total_reports')
                ->values()
                ->toArray();

            // Get zones with no scheduled collections
            $allZones = Route::distinct('zone')->pluck('zone')->toArray();
            $zonesWithCollections = collect($collectionsByZone)->pluck('zone')->toArray();
            $zonesWithoutCollections = array_diff($allZones, $zonesWithCollections);

            return [
                'collections_by_zone' => $collectionsByZone,
                'reports_by_zone' => $reportsByZone,
                'zones_without_collections' => array_values($zonesWithoutCollections),
                'total_zones' => count($allZones),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating geographic distribution', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return $this->getEmptyGeographicDistribution($startDate, $endDate);
        }
    }

    /**
     * Get operational costs summary.
     * Calculates cost summaries if cost data is available.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getOperationalCosts(Carbon $startDate, Carbon $endDate): array
    {
        try {
            // Note: This is a placeholder implementation as cost tracking
            // is not yet implemented in the system. Returns empty data gracefully.
            
            // In a full implementation, this would query cost-related tables
            // for fuel, maintenance, labor costs, etc.
            // Example queries:
            // - FuelCost::forDateRange($startDate, $endDate)->sum('amount')
            // - MaintenanceCost::forDateRange($startDate, $endDate)->sum('amount')
            // - LaborCost::forDateRange($startDate, $endDate)->sum('amount')
            
            // Check if cost tracking tables exist (future implementation)
            $costTrackingAvailable = false; // Set to true when cost tables are implemented
            
            if (!$costTrackingAvailable) {
                return [
                    'available' => false,
                    'message' => 'Cost tracking is not yet implemented. Enable cost tracking to view operational expenses.',
                    'total_costs' => 0,
                    'cost_breakdown' => [
                        'fuel' => 0,
                        'maintenance' => 0,
                        'labor' => 0,
                    ],
                    'cost_per_collection' => 0,
                    'trend_data' => [],
                    'comparison' => [],
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                ];
            }
            
            // Future implementation when cost tracking is enabled:
            // $fuelCosts = FuelCost::forDateRange($startDate, $endDate)->sum('amount');
            // $maintenanceCosts = MaintenanceCost::forDateRange($startDate, $endDate)->sum('amount');
            // $laborCosts = LaborCost::forDateRange($startDate, $endDate)->sum('amount');
            // $totalCosts = $fuelCosts + $maintenanceCosts + $laborCosts;
            
            // Get collection count for cost per collection calculation
            // $collectionCount = CollectionLog::forDateRange($startDate, $endDate)
            //     ->where('status', CollectionLog::STATUS_COMPLETED)
            //     ->count();
            // $costPerCollection = $collectionCount > 0 ? $totalCosts / $collectionCount : 0;
            
            // Generate trend data for chart
            // $trendData = $this->generateCostTrendData($startDate, $endDate);
            
            return [
                'available' => true,
                'total_costs' => 0, // Replace with actual calculation
                'cost_breakdown' => [
                    'fuel' => 0,
                    'maintenance' => 0,
                    'labor' => 0,
                ],
                'cost_per_collection' => 0,
                'trend_data' => [],
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating operational costs', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            
            return [
                'available' => false,
                'message' => 'Error calculating costs. Please try again later.',
                'total_costs' => 0,
                'cost_breakdown' => [
                    'fuel' => 0,
                    'maintenance' => 0,
                    'labor' => 0,
                ],
                'cost_per_collection' => 0,
                'trend_data' => [],
                'comparison' => [],
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
            ];
        }
    }
    
    /**
     * Generate cost trend data for charts (future implementation).
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function generateCostTrendData(Carbon $startDate, Carbon $endDate): array
    {
        // Future implementation when cost tracking is enabled
        // This would generate daily cost data for the trend chart
        
        return [
            'labels' => [],
            'values' => [],
        ];
    }

    /**
     * Generate chart data formatted for Chart.js consumption.
     *
     * @param string $chartType Type of chart (line, bar, pie, doughnut)
     * @param array $rawData Raw data to format
     * @return array
     */
    public function generateChartData(string $chartType, array $rawData): array
    {
        try {
            switch ($chartType) {
                case 'completion_trend':
                    return $this->formatCompletionTrendChart($rawData);
                
                case 'recycling_breakdown':
                    return $this->formatRecyclingBreakdownChart($rawData);
                
                case 'route_performance':
                    return $this->formatRoutePerformanceChart($rawData);
                
                case 'report_status':
                    return $this->formatReportStatusChart($rawData);
                
                case 'zone_activity':
                    return $this->formatZoneActivityChart($rawData);
                
                default:
                    return [
                        'labels' => [],
                        'datasets' => [],
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Error generating chart data', [
                'error' => $e->getMessage(),
                'chart_type' => $chartType,
            ]);
            
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }
    }

    /**
     * Get daily completion rates for trend chart.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getCompletionTrend(Carbon $start, Carbon $end): array
    {
        try {
            $logs = CollectionLog::forDateRange($start, $end)
                ->with('assignment')
                ->get();

            $logsByDate = $logs->groupBy(function ($log) {
                return $log->assignment->assignment_date->format('Y-m-d');
            });

            $period = CarbonPeriod::create($start, $end);
            $trend = [];

            foreach ($period as $date) {
                $dateKey = $date->format('Y-m-d');
                $dailyLogs = $logsByDate->get($dateKey, collect());
                
                $totalLogs = $dailyLogs->count();
                $completedLogs = $dailyLogs->where('status', CollectionLog::STATUS_COMPLETED)->count();
                
                $completionRate = $totalLogs > 0 
                    ? round(($completedLogs / $totalLogs) * 100, 2) 
                    : 0;

                $trend[] = [
                    'date' => $date->format('Y-m-d'),
                    'date_formatted' => $date->format('M d'),
                    'total' => $totalLogs,
                    'completed' => $completedLogs,
                    'completion_rate' => $completionRate,
                ];
            }

            return $trend;
        } catch (\Exception $e) {
            Log::error('Error calculating completion trend', [
                'error' => $e->getMessage(),
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ]);
            
            return [];
        }
    }

    // Private helper methods for chart formatting

    /**
     * Format completion trend data for Chart.js line chart.
     */
    private function formatCompletionTrendChart(array $trendData): array
    {
        $labels = array_column($trendData, 'date_formatted');
        $completionRates = array_column($trendData, 'completion_rate');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Completion Rate (%)',
                    'data' => $completionRates,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Format recycling breakdown data for Chart.js pie chart.
     */
    private function formatRecyclingBreakdownChart(array $materialData): array
    {
        $labels = array_column($materialData, 'material_type');
        $weights = array_column($materialData, 'weight');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Weight (kg)',
                    'data' => $weights,
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                        'rgb(168, 85, 247)',
                        'rgb(236, 72, 153)',
                    ],
                ],
            ],
        ];
    }

    /**
     * Format route performance data for Chart.js bar chart.
     */
    private function formatRoutePerformanceChart(array $routeData): array
    {
        $labels = array_column($routeData, 'route_name');
        $completionRates = array_column($routeData, 'completion_rate');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Completion Rate (%)',
                    'data' => $completionRates,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                ],
            ],
        ];
    }

    /**
     * Format report status data for Chart.js doughnut chart.
     */
    private function formatReportStatusChart(array $statusData): array
    {
        return [
            'labels' => ['Pending', 'In Progress', 'Resolved', 'Closed'],
            'datasets' => [
                [
                    'label' => 'Reports',
                    'data' => [
                        $statusData['pending'] ?? 0,
                        $statusData['in_progress'] ?? 0,
                        $statusData['resolved'] ?? 0,
                        $statusData['closed'] ?? 0,
                    ],
                    'backgroundColor' => [
                        'rgb(251, 191, 36)',
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(156, 163, 175)',
                    ],
                ],
            ],
        ];
    }

    /**
     * Format zone activity data for Chart.js bar chart.
     */
    private function formatZoneActivityChart(array $zoneData): array
    {
        $labels = array_column($zoneData, 'zone');
        $collections = array_column($zoneData, 'total_collections');

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Collections',
                    'data' => $collections,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                ],
            ],
        ];
    }

    // Private helper methods for empty data responses

    /**
     * Return empty collection metrics structure.
     */
    private function getEmptyCollectionMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_collections' => 0,
            'completed' => 0,
            'incomplete' => 0,
            'issues_reported' => 0,
            'completion_rate' => 0,
            'today' => [
                'scheduled' => 0,
                'completed' => 0,
                'issues' => 0,
            ],
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty recycling metrics structure.
     */
    private function getEmptyRecyclingMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_weight' => 0,
            'total_logs' => 0,
            'material_breakdown' => [],
            'recycling_rate' => 0,
            'logs_with_quality_issues' => 0,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty fleet metrics structure.
     */
    private function getEmptyFleetMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_trucks' => 0,
            'operational' => 0,
            'maintenance' => 0,
            'out_of_service' => 0,
            'average_utilization' => 0,
            'trucks_with_assignments' => 0,
            'underutilized_trucks' => [],
            'utilization_by_truck' => [],
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty crew performance structure.
     */
    private function getEmptyCrewPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'active_crew_count' => 0,
            'total_collections' => 0,
            'avg_collections_per_crew' => 0,
            'top_performers' => [],
            'crew_with_most_issues' => [],
            'all_crew_performance' => [],
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty report statistics structure.
     */
    private function getEmptyReportStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_reports' => 0,
            'by_status' => [
                'pending' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'closed' => 0,
            ],
            'by_type' => [],
            'most_common_type' => null,
            'avg_resolution_time_hours' => null,
            'locations_with_most_reports' => [],
            'resolved_count' => 0,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty route performance structure.
     */
    private function getEmptyRoutePerformance(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'all_routes' => [],
            'routes_with_lowest_completion' => [],
            'routes_with_most_issues' => [],
            'total_routes_tracked' => 0,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty usage statistics structure.
     */
    private function getEmptyUsageStatistics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'active_users_by_role' => [
                'administrators' => 0,
                'crew_members' => 0,
                'residents' => 0,
                'total' => 0,
            ],
            'new_resident_registrations' => 0,
            'active_residents' => 0,
            'reports_per_active_resident' => 0,
            'inactive_users_30_days' => 0,
            'recent_logins' => 0,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Return empty geographic distribution structure.
     */
    private function getEmptyGeographicDistribution(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'collections_by_zone' => [],
            'reports_by_zone' => [],
            'zones_without_collections' => [],
            'total_zones' => 0,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
        ];
    }

    /**
     * Get average completion time in minutes for collections.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function getAverageCompletionTime(Carbon $startDate, Carbon $endDate): float
    {
        try {
            $avgMinutes = \App\Models\CollectionLog::forDateRange($startDate, $endDate)
                ->whereNotNull('completion_time')
                ->where('status', \App\Models\CollectionLog::STATUS_COMPLETED)
                ->avg('completion_time');

            return $avgMinutes ? round($avgMinutes, 2) : 0.0;
        } catch (\Exception $e) {
            \Log::error('Error calculating average completion time', [
                'error' => $e->getMessage(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]);
            return 0.0;
        }
    }
}
