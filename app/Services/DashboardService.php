<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\DashboardPreference;
use App\Models\DismissedAlert;
use App\Models\Report;
use App\Models\Truck;
use App\Models\User;
use App\Traits\MonitorsQueryPerformance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    use MonitorsQueryPerformance;

    /**
     * Trend direction constants.
     */
    private const TREND_INCREASING = 'increasing';
    private const TREND_DECREASING = 'decreasing';
    private const TREND_STABLE = 'stable';

    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Get all metrics for admin dashboard with caching.
     * Aggregates comprehensive metrics from all analytics services.
     *
     * @param array $filters Optional filters (date_range, zone, etc.)
     * @return array
     */
    public function getAdminMetrics(array $filters = []): array
    {
        // Parse date range from filters or use defaults
        $dateRange = $this->parseDateRange($filters);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Generate cache key based on filters
        $cacheKey = CacheConfigService::generateKey(
            CacheConfigService::PREFIX_DASHBOARD,
            ['admin_metrics', $filters]
        );

        return $this->executeWithCacheFallback(
            function () use ($startDate, $endDate, $filters, $cacheKey) {
                // Try to get from cache first
                return Cache::remember($cacheKey, CacheConfigService::getTTL('realtime'), function () use ($startDate, $endDate, $filters) {
                    // Collect all metrics with individual error handling
                    $metrics = [];
                    
                    try {
                        $metrics['collection_metrics'] = $this->analyticsService->getCollectionMetrics($startDate, $endDate, $filters);
                    } catch (\Exception $e) {
                        Log::error('Failed to load collection metrics', ['error' => $e->getMessage()]);
                        $metrics['collection_metrics'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'collection metrics')];
                    }

                    try {
                        $metrics['recycling_metrics'] = $this->analyticsService->getRecyclingMetrics($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load recycling metrics', ['error' => $e->getMessage()]);
                        $metrics['recycling_metrics'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'recycling metrics')];
                    }

                    try {
                        $metrics['fleet_metrics'] = $this->analyticsService->getFleetMetrics($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load fleet metrics', ['error' => $e->getMessage()]);
                        $metrics['fleet_metrics'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'fleet metrics')];
                    }

                    try {
                        $metrics['crew_performance'] = $this->analyticsService->getCrewPerformance($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load crew performance', ['error' => $e->getMessage()]);
                        $metrics['crew_performance'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'crew performance')];
                    }

                    try {
                        $metrics['report_statistics'] = $this->analyticsService->getReportStatistics($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load report statistics', ['error' => $e->getMessage()]);
                        $metrics['report_statistics'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'report statistics')];
                    }

                    try {
                        $metrics['route_performance'] = $this->analyticsService->getRoutePerformance($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load route performance', ['error' => $e->getMessage()]);
                        $metrics['route_performance'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'route performance')];
                    }

                    try {
                        $metrics['usage_statistics'] = $this->analyticsService->getUsageStatistics($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load usage statistics', ['error' => $e->getMessage()]);
                        $metrics['usage_statistics'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'usage statistics')];
                    }

                    try {
                        $metrics['geographic_distribution'] = $this->analyticsService->getGeographicDistribution($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load geographic distribution', ['error' => $e->getMessage()]);
                        $metrics['geographic_distribution'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'geographic distribution')];
                    }

                    try {
                        $metrics['operational_costs'] = $this->analyticsService->getOperationalCosts($startDate, $endDate);
                    } catch (\Exception $e) {
                        Log::error('Failed to load operational costs', ['error' => $e->getMessage()]);
                        $metrics['operational_costs'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'operational costs')];
                    }

                    // Add pending items that require attention
                    try {
                        $metrics['pending_items'] = $this->getPendingItems();
                    } catch (\Exception $e) {
                        Log::error('Failed to load pending items', ['error' => $e->getMessage()]);
                        $metrics['pending_items'] = [
                            'unassigned_routes' => 0,
                            'pending_reports' => 0,
                            'trucks_in_maintenance' => 0,
                            'overdue_reports' => 0,
                            'error' => $this->getUserFriendlyErrorMessage($e, 'pending items'),
                        ];
                    }

                    // Add alerts
                    try {
                        $metrics['alerts'] = $this->getAlerts();
                    } catch (\Exception $e) {
                        Log::error('Failed to load alerts', ['error' => $e->getMessage()]);
                        $metrics['alerts'] = [];
                    }

                    // Add period comparison if requested
                    if (isset($filters['compare_period']) && $filters['compare_period']) {
                        try {
                            $metrics['comparisons'] = $this->getComparisonMetrics($startDate, $endDate, $filters['compare_period']);
                        } catch (\Exception $e) {
                            Log::error('Failed to load comparison metrics', ['error' => $e->getMessage()]);
                            $metrics['comparisons'] = ['error' => $this->getUserFriendlyErrorMessage($e, 'comparison data')];
                        }
                    }

                    // Add metadata
                    $metrics['metadata'] = [
                        'generated_at' => now()->toIso8601String(),
                        'period_start' => $startDate->format('Y-m-d'),
                        'period_end' => $endDate->format('Y-m-d'),
                        'filters_applied' => $filters,
                    ];

                    return $metrics;
                });
            },
            $cacheKey,
            'admin_dashboard_metrics',
            $this->getEmptyAdminMetrics($filters)
        );
    }

    /**
     * Get comparison metrics for a previous period.
     * Calculates metrics for a comparison period and generates comparisons.
     *
     * @param Carbon $currentStart Current period start date
     * @param Carbon $currentEnd Current period end date
     * @param string $comparisonType Type of comparison (previous_week, previous_month, previous_quarter, previous_year)
     * @return array
     */
    public function getComparisonMetrics(Carbon $currentStart, Carbon $currentEnd, string $comparisonType): array
    {
        try {
            // Calculate previous period dates
            $previousPeriod = $this->calculatePreviousPeriod($currentStart, $currentEnd, $comparisonType);
            $previousStart = $previousPeriod['start'];
            $previousEnd = $previousPeriod['end'];

            // Get metrics for previous period
            $previousMetrics = [
                'collection_metrics' => $this->analyticsService->getCollectionMetrics($previousStart, $previousEnd),
                'recycling_metrics' => $this->analyticsService->getRecyclingMetrics($previousStart, $previousEnd),
                'fleet_metrics' => $this->analyticsService->getFleetMetrics($previousStart, $previousEnd),
                'report_statistics' => $this->analyticsService->getReportStatistics($previousStart, $previousEnd),
                'operational_costs' => $this->analyticsService->getOperationalCosts($previousStart, $previousEnd),
            ];

            // Get current metrics for comparison
            $currentMetrics = [
                'collection_metrics' => $this->analyticsService->getCollectionMetrics($currentStart, $currentEnd),
                'recycling_metrics' => $this->analyticsService->getRecyclingMetrics($currentStart, $currentEnd),
                'fleet_metrics' => $this->analyticsService->getFleetMetrics($currentStart, $currentEnd),
                'report_statistics' => $this->analyticsService->getReportStatistics($currentStart, $currentEnd),
                'operational_costs' => $this->analyticsService->getOperationalCosts($currentStart, $currentEnd),
            ];

            // Generate comparisons
            $comparisons = $this->generateComparisons($currentMetrics, $previousMetrics);

            // Add period information
            $comparisons['period_info'] = [
                'current_start' => $currentStart->format('Y-m-d'),
                'current_end' => $currentEnd->format('Y-m-d'),
                'previous_start' => $previousStart->format('Y-m-d'),
                'previous_end' => $previousEnd->format('Y-m-d'),
                'comparison_type' => $comparisonType,
            ];

            return $comparisons;
        } catch (\Exception $e) {
            Log::error('Error getting comparison metrics', [
                'error' => $e->getMessage(),
                'comparison_type' => $comparisonType,
            ]);

            return [];
        }
    }

    /**
     * Calculate previous period dates based on comparison type.
     *
     * @param Carbon $currentStart Current period start
     * @param Carbon $currentEnd Current period end
     * @param string $comparisonType Comparison type
     * @return array
     */
    private function calculatePreviousPeriod(Carbon $currentStart, Carbon $currentEnd, string $comparisonType): array
    {
        $daysDiff = $currentStart->diffInDays($currentEnd) + 1;

        return match ($comparisonType) {
            'previous_week' => [
                'start' => $currentStart->copy()->subWeek(),
                'end' => $currentEnd->copy()->subWeek(),
            ],
            'previous_month' => [
                'start' => $currentStart->copy()->subMonth(),
                'end' => $currentEnd->copy()->subMonth(),
            ],
            'previous_quarter' => [
                'start' => $currentStart->copy()->subMonths(3),
                'end' => $currentEnd->copy()->subMonths(3),
            ],
            'previous_year' => [
                'start' => $currentStart->copy()->subYear(),
                'end' => $currentEnd->copy()->subYear(),
            ],
            default => [
                'start' => $currentStart->copy()->subDays($daysDiff),
                'end' => $currentEnd->copy()->subDays($daysDiff),
            ],
        };
    }

    /**
     * Get metrics for crew dashboard.
     * Retrieves crew-specific dashboard data focused on assignments and performance.
     *
     * @param User $crewMember
     * @return array
     */
    public function getCrewMetrics(User $crewMember): array
    {
        try {
            $cacheKey = CacheConfigService::generateKey(
                CacheConfigService::PREFIX_DASHBOARD,
                ['crew_metrics', $crewMember->id]
            );

            return Cache::remember($cacheKey, CacheConfigService::getTTL('realtime'), function () use ($crewMember) {
                // Get today's assignment
                $todayAssignment = Assignment::active()
                    ->where('user_id', $crewMember->id)
                    ->forDate(now())
                    ->with(['route', 'truck'])
                    ->first();

                // Get upcoming assignments (next 7 days)
                $upcomingAssignments = Assignment::active()
                    ->where('user_id', $crewMember->id)
                    ->where('assignment_date', '>', now())
                    ->where('assignment_date', '<=', now()->addDays(7))
                    ->with(['route', 'truck'])
                    ->orderBy('assignment_date')
                    ->get();

                // Get crew member's performance for the last 30 days
                $startDate = now()->subDays(30);
                $endDate = now();
                
                $crewPerformance = $this->analyticsService->getCrewPerformance($startDate, $endDate);
                $memberPerformance = collect($crewPerformance['all_crew_performance'])
                    ->firstWhere('user_id', $crewMember->id);

                // Get recent collection logs
                $recentLogs = $crewMember->collectionLogs()
                    ->with(['assignment.route'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();

                return [
                    'today_assignment' => $todayAssignment ? [
                        'id' => $todayAssignment->id,
                        'route_name' => $todayAssignment->route->name,
                        'route_zone' => $todayAssignment->route->zone,
                        'truck_number' => $todayAssignment->truck->truck_number,
                        'assignment_date' => $todayAssignment->assignment_date->format('Y-m-d'),
                    ] : null,
                    'upcoming_assignments' => $upcomingAssignments->map(fn($a) => [
                        'id' => $a->id,
                        'route_name' => $a->route->name,
                        'truck_number' => $a->truck->truck_number,
                        'assignment_date' => $a->assignment_date->format('Y-m-d'),
                    ]),
                    'performance' => $memberPerformance ?? [
                        'total_collections' => 0,
                        'completed' => 0,
                        'completion_rate' => 0,
                    ],
                    'recent_logs' => $recentLogs->map(fn($log) => [
                        'id' => $log->id,
                        'route_name' => $log->assignment->route->name ?? 'Unknown',
                        'status' => $log->status,
                        'logged_at' => $log->created_at->format('Y-m-d H:i'),
                        'notes' => $log->notes,
                    ]),
                    'metadata' => [
                        'generated_at' => now()->toIso8601String(),
                        'crew_member_id' => $crewMember->id,
                        'crew_member_name' => $crewMember->name,
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error getting crew metrics', [
                'error' => $e->getMessage(),
                'crew_member_id' => $crewMember->id,
            ]);

            return $this->getEmptyCrewMetrics($crewMember);
        }
    }

    /**
     * Get metrics for resident dashboard.
     * Retrieves resident-specific dashboard data focused on schedules and reports.
     *
     * @param User $resident
     * @return array
     */
    public function getResidentMetrics(User $resident): array
    {
        try {
            $cacheKey = CacheConfigService::generateKey(
                CacheConfigService::PREFIX_DASHBOARD,
                ['resident_metrics', $resident->id]
            );

            return Cache::remember($cacheKey, CacheConfigService::getTTL('realtime'), function () use ($resident) {
                // Get resident's zone from their profile or reports
                $zone = $this->getResidentZone($resident);

                // Get next scheduled collection for their zone
                $nextCollection = null;
                if ($zone) {
                    $nextAssignment = Assignment::active()
                        ->whereHas('route', fn($q) => $q->where('zone', $zone))
                        ->where('assignment_date', '>=', now())
                        ->orderBy('assignment_date')
                        ->with(['route', 'truck'])
                        ->first();

                    if ($nextAssignment) {
                        $nextCollection = [
                            'date' => $nextAssignment->assignment_date->format('Y-m-d'),
                            'date_formatted' => $nextAssignment->assignment_date->format('l, F j, Y'),
                            'route_name' => $nextAssignment->route->name,
                            'days_until' => (int) now()->startOfDay()->diffInDays($nextAssignment->assignment_date->startOfDay(), false),
                        ];
                    }
                }

                // Get resident's recent reports
                $recentReports = $resident->reports()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                // Get collection schedule for the zone (next 30 days)
                $schedule = [];
                if ($zone) {
                    $schedule = Assignment::active()
                        ->whereHas('route', fn($q) => $q->where('zone', $zone))
                        ->whereBetween('assignment_date', [now(), now()->addDays(30)])
                        ->with('route')
                        ->orderBy('assignment_date')
                        ->get()
                        ->map(fn($a) => [
                            'date' => $a->assignment_date->format('Y-m-d'),
                            'date_formatted' => $a->assignment_date->format('M d, Y'),
                            'route_name' => $a->route->name,
                        ]);
                }

                return [
                    'zone' => $zone,
                    'next_collection' => $nextCollection,
                    'recent_reports' => $recentReports->map(fn($r) => [
                        'id' => $r->id,
                        'reference_number' => $r->reference_number,
                        'type' => $r->report_type,
                        'type_label' => Report::REPORT_TYPES[$r->report_type] ?? 'Unknown',
                        'status' => $r->status,
                        'status_label' => Report::STATUSES[$r->status] ?? 'Unknown',
                        'submitted_at' => $r->created_at->format('Y-m-d H:i'),
                        'description' => $r->description,
                    ]),
                    'collection_schedule' => $schedule,
                    'report_statistics' => [
                        'total_reports' => $resident->reports()->count(),
                        'pending_reports' => $resident->reports()->pending()->count(),
                        'resolved_reports' => $resident->reports()->resolved()->count(),
                    ],
                    'metadata' => [
                        'generated_at' => now()->toIso8601String(),
                        'resident_id' => $resident->id,
                        'resident_name' => $resident->name,
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error getting resident metrics', [
                'error' => $e->getMessage(),
                'resident_id' => $resident->id,
            ]);

            return $this->getEmptyResidentMetrics($resident);
        }
    }

    /**
     * Calculate trend indicators for a metric.
     * Compares current value to previous period to determine direction.
     *
     * @param string $metric Metric name
     * @param array $periods Array with 'current' and 'previous' values
     * @return array
     */
    public function calculateTrends(string $metric, array $periods): array
    {
        try {
            $current = $periods['current'] ?? 0;
            $previous = $periods['previous'] ?? 0;

            // Calculate percentage change
            $percentageChange = 0;
            if ($previous > 0) {
                $percentageChange = round((($current - $previous) / $previous) * 100, 2);
            } elseif ($current > 0) {
                $percentageChange = 100; // If previous was 0 and current is positive
            }

            // Determine trend direction
            $trend = self::TREND_STABLE;
            $threshold = 5; // 5% threshold for considering a change significant

            if (abs($percentageChange) >= $threshold) {
                $trend = $percentageChange > 0 ? self::TREND_INCREASING : self::TREND_DECREASING;
            }

            return [
                'metric' => $metric,
                'current_value' => $current,
                'previous_value' => $previous,
                'change' => $current - $previous,
                'percentage_change' => $percentageChange,
                'trend' => $trend,
                'is_improving' => $this->isImprovingTrend($metric, $trend),
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating trends', [
                'error' => $e->getMessage(),
                'metric' => $metric,
            ]);

            return [
                'metric' => $metric,
                'current_value' => $periods['current'] ?? 0,
                'previous_value' => $periods['previous'] ?? 0,
                'change' => 0,
                'percentage_change' => 0,
                'trend' => self::TREND_STABLE,
                'is_improving' => null,
            ];
        }
    }

    /**
     * Generate period-over-period comparisons with percentage changes.
     * Compares current data to previous period data for all KPIs.
     *
     * @param array $currentData Current period metrics
     * @param array $previousData Previous period metrics
     * @return array
     */
    public function generateComparisons(array $currentData, array $previousData): array
    {
        try {
            $comparisons = [];

            // Collection metrics comparisons
            if (isset($currentData['collection_metrics']) && isset($previousData['collection_metrics'])) {
                $comparisons['collections'] = [
                    'total' => $this->calculateTrends('total_collections', [
                        'current' => $currentData['collection_metrics']['total_collections'],
                        'previous' => $previousData['collection_metrics']['total_collections'],
                    ]),
                    'completion_rate' => $this->calculateTrends('completion_rate', [
                        'current' => $currentData['collection_metrics']['completion_rate'],
                        'previous' => $previousData['collection_metrics']['completion_rate'],
                    ]),
                    'issues' => $this->calculateTrends('issues_reported', [
                        'current' => $currentData['collection_metrics']['issues_reported'],
                        'previous' => $previousData['collection_metrics']['issues_reported'],
                    ]),
                ];
            }

            // Recycling metrics comparisons
            if (isset($currentData['recycling_metrics']) && isset($previousData['recycling_metrics'])) {
                $comparisons['recycling'] = [
                    'total_weight' => $this->calculateTrends('total_weight', [
                        'current' => $currentData['recycling_metrics']['total_weight'],
                        'previous' => $previousData['recycling_metrics']['total_weight'],
                    ]),
                    'recycling_rate' => $this->calculateTrends('recycling_rate', [
                        'current' => $currentData['recycling_metrics']['recycling_rate'],
                        'previous' => $previousData['recycling_metrics']['recycling_rate'],
                    ]),
                ];
            }

            // Fleet metrics comparisons
            if (isset($currentData['fleet_metrics']) && isset($previousData['fleet_metrics'])) {
                $comparisons['fleet'] = [
                    'utilization' => $this->calculateTrends('average_utilization', [
                        'current' => $currentData['fleet_metrics']['average_utilization'],
                        'previous' => $previousData['fleet_metrics']['average_utilization'],
                    ]),
                    'operational_trucks' => $this->calculateTrends('operational_trucks', [
                        'current' => $currentData['fleet_metrics']['operational'],
                        'previous' => $previousData['fleet_metrics']['operational'],
                    ]),
                ];
            }

            // Report statistics comparisons
            if (isset($currentData['report_statistics']) && isset($previousData['report_statistics'])) {
                $comparisons['reports'] = [
                    'total' => $this->calculateTrends('total_reports', [
                        'current' => $currentData['report_statistics']['total_reports'],
                        'previous' => $previousData['report_statistics']['total_reports'],
                    ]),
                    'avg_resolution_time' => $this->calculateTrends('avg_resolution_time', [
                        'current' => $currentData['report_statistics']['avg_resolution_time_hours'] ?? 0,
                        'previous' => $previousData['report_statistics']['avg_resolution_time_hours'] ?? 0,
                    ]),
                ];
            }

            // Operational costs comparisons (if available)
            if (isset($currentData['operational_costs']) && isset($previousData['operational_costs'])) {
                if (($currentData['operational_costs']['available'] ?? false) && ($previousData['operational_costs']['available'] ?? false)) {
                    $comparisons['operational_costs'] = [
                        'total_costs' => $this->calculateTrends('total_costs', [
                            'current' => $currentData['operational_costs']['total_costs'],
                            'previous' => $previousData['operational_costs']['total_costs'],
                        ]),
                        'cost_per_collection' => $this->calculateTrends('cost_per_collection', [
                            'current' => $currentData['operational_costs']['cost_per_collection'],
                            'previous' => $previousData['operational_costs']['cost_per_collection'],
                        ]),
                        'fuel_costs' => $this->calculateTrends('fuel_costs', [
                            'current' => $currentData['operational_costs']['cost_breakdown']['fuel'] ?? 0,
                            'previous' => $previousData['operational_costs']['cost_breakdown']['fuel'] ?? 0,
                        ]),
                        'maintenance_costs' => $this->calculateTrends('maintenance_costs', [
                            'current' => $currentData['operational_costs']['cost_breakdown']['maintenance'] ?? 0,
                            'previous' => $previousData['operational_costs']['cost_breakdown']['maintenance'] ?? 0,
                        ]),
                    ];
                }
            }

            return $comparisons;
        } catch (\Exception $e) {
            Log::error('Error generating comparisons', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get user dashboard preferences.
     * Returns user's saved preferences or defaults if none exist.
     *
     * @param User $user
     * @return array
     */
    public function getUserPreferences(User $user): array
    {
        try {
            $preference = $user->dashboardPreference;

            if (!$preference) {
                // Return default preferences
                return [
                    'widget_visibility' => DashboardPreference::getDefaultWidgetVisibility(),
                    'widget_order' => DashboardPreference::getDefaultWidgetOrder(),
                    'default_filters' => [],
                    'default_view' => null,
                ];
            }

            return [
                'widget_visibility' => $preference->widget_visibility ?? DashboardPreference::getDefaultWidgetVisibility(),
                'widget_order' => $preference->widget_order ?? DashboardPreference::getDefaultWidgetOrder(),
                'default_filters' => $preference->default_filters ?? [],
                'default_view' => $preference->default_view,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting user preferences', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'widget_visibility' => DashboardPreference::getDefaultWidgetVisibility(),
                'widget_order' => DashboardPreference::getDefaultWidgetOrder(),
                'default_filters' => [],
                'default_view' => null,
            ];
        }
    }

    /**
     * Save user dashboard preferences.
     * Creates or updates user's dashboard customization settings.
     *
     * @param User $user
     * @param array $preferences
     * @return bool
     */
    public function saveUserPreferences(User $user, array $preferences): bool
    {
        try {
            $dashboardPreference = $user->dashboardPreference;

            if (!$dashboardPreference) {
                $dashboardPreference = new DashboardPreference(['user_id' => $user->id]);
            }

            // Update only provided fields
            if (isset($preferences['widget_visibility'])) {
                $dashboardPreference->widget_visibility = $preferences['widget_visibility'];
            }

            if (isset($preferences['widget_order'])) {
                $dashboardPreference->widget_order = $preferences['widget_order'];
            }

            if (isset($preferences['default_filters'])) {
                $dashboardPreference->default_filters = $preferences['default_filters'];
            }

            if (isset($preferences['default_view'])) {
                $dashboardPreference->default_view = $preferences['default_view'];
            }

            $saved = $dashboardPreference->save();

            // Invalidate user's dashboard cache
            $this->invalidateUserCache($user);

            return $saved;
        } catch (\Exception $e) {
            Log::error('Error saving user preferences', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return false;
        }
    }

    // Private helper methods

    /**
     * Parse date range from filters or return defaults.
     */
    private function parseDateRange(array $filters): array
    {
        $period = $filters['period'] ?? '30days';

        switch ($period) {
            case '7days':
                $start = now()->subDays(7);
                break;
            case '30days':
                $start = now()->subDays(30);
                break;
            case '90days':
                $start = now()->subDays(90);
                break;
            case 'custom':
                $start = isset($filters['start_date']) 
                    ? Carbon::parse($filters['start_date']) 
                    : now()->subDays(30);
                $end = isset($filters['end_date']) 
                    ? Carbon::parse($filters['end_date']) 
                    : now();
                return ['start' => $start, 'end' => $end];
            default:
                $start = now()->subDays(30);
        }

        return ['start' => $start, 'end' => now()];
    }

    /**
     * Generate cache key based on context and filters.
     */
    private function generateCacheKey(string $context, array $filters): string
    {
        $filterHash = md5(json_encode($filters));
        return "dashboard:{$context}:{$filterHash}";
    }

    /**
     * Get pending items that require attention.
     */
    private function getPendingItems(): array
    {
        try {
            // Unassigned routes in next 7 days
            $unassignedRoutes = Assignment::active()
                ->whereBetween('assignment_date', [now(), now()->addDays(7)])
                ->whereNull('user_id')
                ->count();

            // Pending reports
            $pendingReports = Report::pending()->count();

            // Trucks in maintenance or out of service
            $trucksInMaintenance = Truck::where('operational_status', Truck::STATUS_MAINTENANCE)
                ->orWhere('operational_status', Truck::STATUS_OUT_OF_SERVICE)
                ->count();

            // Overdue reports (pending for more than 48 hours)
            $overdueReports = Report::pending()
                ->where('created_at', '<', now()->subHours(48))
                ->count();

            return [
                'unassigned_routes' => $unassignedRoutes,
                'pending_reports' => $pendingReports,
                'trucks_in_maintenance' => $trucksInMaintenance,
                'overdue_reports' => $overdueReports,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting pending items', [
                'error' => $e->getMessage(),
            ]);

            return [
                'unassigned_routes' => 0,
                'pending_reports' => 0,
                'trucks_in_maintenance' => 0,
                'overdue_reports' => 0,
            ];
        }
    }

    /**
     * Get alerts for the dashboard.
     * Filters out dismissed alerts for the current user.
     */
    private function getAlerts(): array
    {
        try {
            $alerts = [];
            $userId = Auth::id();

            // Unassigned routes within 3 days
            $urgentUnassigned = Assignment::active()
                ->whereBetween('assignment_date', [now(), now()->addDays(3)])
                ->whereNull('user_id')
                ->with('route')
                ->get();

            foreach ($urgentUnassigned as $assignment) {
                $identifier = "assignment_{$assignment->id}";
                
                // Skip if dismissed
                if ($userId && DismissedAlert::isAlertDismissed($userId, 'unassigned_route', $identifier)) {
                    continue;
                }

                $alerts[] = [
                    'id' => $identifier,
                    'type' => 'warning',
                    'category' => 'unassigned_route',
                    'title' => 'Unassigned Route',
                    'message' => "Route '{$assignment->route->name}' is unassigned for {$assignment->assignment_date->format('M d, Y')}",
                    'link' => route('assignments.index'),
                    'linkText' => 'Manage assignments',
                    'priority' => 'high',
                    'timestamp' => $assignment->created_at->toIso8601String(),
                ];
            }

            // Overdue reports (pending for more than 48 hours)
            $overdueReports = Report::where('status', Report::STATUS_PENDING)
                ->where('created_at', '<', now()->subHours(48))
                ->orderBy('created_at', 'asc')
                ->limit(10)
                ->get();

            foreach ($overdueReports as $report) {
                $identifier = "report_{$report->id}";
                
                // Skip if dismissed
                if ($userId && DismissedAlert::isAlertDismissed($userId, 'overdue_report', $identifier)) {
                    continue;
                }

                $alerts[] = [
                    'id' => $identifier,
                    'type' => 'danger',
                    'category' => 'overdue_report',
                    'title' => 'Overdue Report',
                    'message' => "Report {$report->reference_number} is overdue (submitted {$report->created_at->diffForHumans()})",
                    'link' => route('reports.show', $report),
                    'linkText' => 'View report',
                    'priority' => 'high',
                    'timestamp' => $report->created_at->toIso8601String(),
                ];
            }

            // Trucks requiring maintenance
            $maintenanceTrucks = Truck::where('operational_status', Truck::STATUS_MAINTENANCE)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($maintenanceTrucks as $truck) {
                $identifier = "truck_{$truck->id}";
                
                // Skip if dismissed
                if ($userId && DismissedAlert::isAlertDismissed($userId, 'truck_maintenance', $identifier)) {
                    continue;
                }

                $alerts[] = [
                    'id' => $identifier,
                    'type' => 'info',
                    'category' => 'truck_maintenance',
                    'title' => 'Truck Maintenance',
                    'message' => "Truck {$truck->truck_number} is currently in maintenance",
                    'link' => route('trucks.show', $truck),
                    'linkText' => 'View truck details',
                    'priority' => 'medium',
                    'timestamp' => $truck->updated_at->toIso8601String(),
                ];
            }

            // Sort by priority (high first) and timestamp
            usort($alerts, function ($a, $b) {
                $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
                $aPriority = $priorityOrder[$a['priority']] ?? 3;
                $bPriority = $priorityOrder[$b['priority']] ?? 3;
                
                if ($aPriority !== $bPriority) {
                    return $aPriority <=> $bPriority;
                }
                
                return strcmp($a['timestamp'], $b['timestamp']);
            });

            return $alerts;
        } catch (\Exception $e) {
            Log::error('Error getting alerts', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Determine if a trend is improving based on metric type.
     */
    private function isImprovingTrend(string $metric, string $trend): ?bool
    {
        // Metrics where increasing is good
        $increasingIsGood = [
            'completion_rate',
            'total_collections',
            'completed',
            'recycling_rate',
            'total_weight',
            'average_utilization',
            'operational_trucks',
        ];

        // Metrics where decreasing is good (lower is better)
        $decreasingIsGood = [
            'issues_reported',
            'incomplete',
            'avg_resolution_time',
            'pending_reports',
            'overdue_reports',
            'total_costs',
            'cost_per_collection',
            'fuel_costs',
            'maintenance_costs',
        ];

        if (in_array($metric, $increasingIsGood)) {
            return $trend === self::TREND_INCREASING;
        }

        if (in_array($metric, $decreasingIsGood)) {
            return $trend === self::TREND_DECREASING;
        }

        // For neutral metrics, return null
        return null;
    }

    /**
     * Get resident's zone from their profile or most recent report.
     */
    private function getResidentZone(User $resident): ?string
    {
        // Try to get zone from most recent report
        $recentReport = $resident->reports()
            ->whereNotNull('route_id')
            ->with('route')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($recentReport && $recentReport->route) {
            return $recentReport->route->zone;
        }

        // Could also check a profile field if implemented
        // return $resident->profile->zone ?? null;

        return null;
    }

    /**
     * Invalidate all cache entries for a user.
     */
    private function invalidateUserCache(User $user): void
    {
        try {
            if ($user->hasRole('administrator')) {
                // Clear admin dashboard cache (all filter combinations would be complex,
                // so we use cache tags if available, or just clear specific known keys)
                Cache::forget("dashboard:admin_metrics:" . md5(json_encode([])));
            } elseif ($user->hasRole('collection_crew')) {
                Cache::forget("crew_metrics:{$user->id}");
            } elseif ($user->hasRole('resident')) {
                Cache::forget("resident_metrics:{$user->id}");
            }
        } catch (\Exception $e) {
            Log::error('Error invalidating user cache', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Get empty admin metrics structure.
     */
    private function getEmptyAdminMetrics(array $filters): array
    {
        $dateRange = $this->parseDateRange($filters);
        
        return [
            'collection_metrics' => [],
            'recycling_metrics' => [],
            'fleet_metrics' => [],
            'crew_performance' => [],
            'report_statistics' => [],
            'route_performance' => [],
            'usage_statistics' => [],
            'geographic_distribution' => [],
            'operational_costs' => [],
            'pending_items' => [
                'unassigned_routes' => 0,
                'pending_reports' => 0,
                'trucks_in_maintenance' => 0,
                'overdue_reports' => 0,
            ],
            'alerts' => [],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'period_start' => $dateRange['start']->format('Y-m-d'),
                'period_end' => $dateRange['end']->format('Y-m-d'),
                'filters_applied' => $filters,
                'error' => true,
            ],
        ];
    }

    /**
     * Get empty crew metrics structure.
     */
    private function getEmptyCrewMetrics(User $crewMember): array
    {
        return [
            'today_assignment' => null,
            'upcoming_assignments' => [],
            'performance' => [
                'total_collections' => 0,
                'completed' => 0,
                'completion_rate' => 0,
            ],
            'recent_logs' => [],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'crew_member_id' => $crewMember->id,
                'crew_member_name' => $crewMember->name,
                'error' => true,
            ],
        ];
    }

    /**
     * Get empty resident metrics structure.
     */
    private function getEmptyResidentMetrics(User $resident): array
    {
        return [
            'zone' => null,
            'next_collection' => null,
            'recent_reports' => [],
            'collection_schedule' => [],
            'report_statistics' => [
                'total_reports' => 0,
                'pending_reports' => 0,
                'resolved_reports' => 0,
            ],
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'resident_id' => $resident->id,
                'resident_name' => $resident->name,
                'error' => true,
            ],
        ];
    }

    /**
     * Get detailed drill-down data for a specific metric.
     * Provides granular data when users click on dashboard metrics.
     *
     * @param string $metric Metric identifier
     * @param array $filters Optional filters
     * @param User $user Current user for authorization context
     * @return array
     */
    public function getDrillDownData(string $metric, array $filters, User $user): array
    {
        try {
            $dateRange = $this->parseDateRange($filters);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

            return match ($metric) {
                'collections_today' => $this->getCollectionsDrillDown($startDate, $endDate, $filters),
                'pending_reports' => $this->getPendingReportsDrillDown($filters),
                'fleet_utilization' => $this->getFleetUtilizationDrillDown($startDate, $endDate),
                'crew_performance' => $this->getCrewPerformanceDrillDown($startDate, $endDate),
                'recycling_metrics' => $this->getRecyclingDrillDown($startDate, $endDate),
                'route_performance' => $this->getRoutePerformanceDrillDown($startDate, $endDate),
                'report_statistics' => $this->getReportStatisticsDrillDown($startDate, $endDate),
                default => ['error' => 'Unknown metric type'],
            };
        } catch (\Exception $e) {
            Log::error('Error getting drill-down data', [
                'error' => $e->getMessage(),
                'metric' => $metric,
                'user_id' => $user->id,
            ]);

            return ['error' => 'Failed to load detailed data'];
        }
    }

    /**
     * Get detailed collections drill-down data.
     */
    private function getCollectionsDrillDown(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $query = Assignment::active()
            ->whereBetween('assignment_date', [$startDate, $endDate])
            ->with(['route', 'truck', 'user']);

        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        if (isset($filters['zone_id'])) {
            $query->whereHas('route', fn($q) => $q->where('zone', $filters['zone_id']));
        }

        $assignments = $query->orderBy('assignment_date', 'desc')->get();

        // Count assignments with collection logs (completed)
        $completedCount = $assignments->filter(function($a) {
            return $a->collectionLogs()->exists();
        })->count();
        
        return [
            'total' => $assignments->count(),
            'completed' => $completedCount,
            'pending' => $assignments->count() - $completedCount,
            'details' => $assignments->map(fn($a) => [
                'id' => $a->id,
                'date' => $a->assignment_date->format('Y-m-d'),
                'route_name' => $a->route->name,
                'truck_number' => $a->truck->truck_number,
                'crew_member' => $a->user?->name ?? 'Unassigned',
                'status' => $a->status,
                'has_log' => $a->collectionLogs()->exists(),
            ]),
        ];
    }

    /**
     * Get detailed pending reports drill-down data.
     */
    private function getPendingReportsDrillDown(array $filters): array
    {
        $query = Report::pending()->with(['user', 'route']);

        if (isset($filters['zone_id'])) {
            $query->whereHas('route', fn($q) => $q->where('zone', $filters['zone_id']));
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        return [
            'total' => $reports->count(),
            'overdue' => $reports->where('created_at', '<', now()->subHours(48))->count(),
            'details' => $reports->map(fn($r) => [
                'id' => $r->id,
                'reference_number' => $r->reference_number,
                'type' => Report::REPORT_TYPES[$r->report_type] ?? 'Unknown',
                'submitted_by' => $r->user->name,
                'submitted_at' => $r->created_at->format('Y-m-d H:i'),
                'age_hours' => $r->created_at->diffInHours(now()),
                'description' => $r->description,
            ]),
        ];
    }

    /**
     * Get detailed fleet utilization drill-down data.
     */
    private function getFleetUtilizationDrillDown(Carbon $startDate, Carbon $endDate): array
    {
        $trucks = Truck::with(['assignments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('assignment_date', [$startDate, $endDate]);
        }])->get();

        return [
            'total_trucks' => $trucks->count(),
            'operational' => $trucks->where('operational_status', Truck::STATUS_OPERATIONAL)->count(),
            'maintenance' => $trucks->where('operational_status', Truck::STATUS_MAINTENANCE)->count(),
            'out_of_service' => $trucks->where('operational_status', Truck::STATUS_OUT_OF_SERVICE)->count(),
            'details' => $trucks->map(fn($t) => [
                'id' => $t->id,
                'truck_number' => $t->truck_number,
                'status' => $t->operational_status,
                'assignments_count' => $t->assignments->count(),
                'last_assignment' => $t->assignments->sortByDesc('assignment_date')->first()?->assignment_date->format('Y-m-d'),
            ]),
        ];
    }

    /**
     * Get detailed crew performance drill-down data.
     */
    private function getCrewPerformanceDrillDown(Carbon $startDate, Carbon $endDate): array
    {
        $crewMembers = User::role('collection_crew')
            ->with(['assignments' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('assignment_date', [$startDate, $endDate]);
            }])
            ->get();

        return [
            'total_crew' => $crewMembers->count(),
            'details' => $crewMembers->map(function ($crew) {
                $assignments = $crew->assignments;
                $total = $assignments->count();
                
                // Count assignments with collection logs
                $completed = $assignments->filter(function($a) {
                    return $a->collectionLogs()->exists();
                })->count();
                
                return [
                    'id' => $crew->id,
                    'name' => $crew->name,
                    'email' => $crew->email,
                    'total_assignments' => $total,
                    'completed' => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                ];
            })->sortByDesc('completion_rate')->values(),
        ];
    }

    /**
     * Get detailed recycling drill-down data.
     */
    private function getRecyclingDrillDown(Carbon $startDate, Carbon $endDate): array
    {
        $recyclingMetrics = $this->analyticsService->getRecyclingMetrics($startDate, $endDate);
        
        return [
            'total_weight' => $recyclingMetrics['total_weight'] ?? 0,
            'recycling_rate' => $recyclingMetrics['recycling_rate'] ?? 0,
            'by_material' => $recyclingMetrics['by_material_type'] ?? [],
            'trend_data' => $recyclingMetrics['trend_data'] ?? [],
        ];
    }

    /**
     * Get detailed route performance drill-down data.
     */
    private function getRoutePerformanceDrillDown(Carbon $startDate, Carbon $endDate): array
    {
        $routeMetrics = $this->analyticsService->getRoutePerformance($startDate, $endDate);
        
        return [
            'total_routes' => $routeMetrics['total_routes'] ?? 0,
            'routes' => $routeMetrics['route_details'] ?? [],
            'lowest_performers' => $routeMetrics['lowest_completion_rates'] ?? [],
            'most_issues' => $routeMetrics['routes_with_most_issues'] ?? [],
        ];
    }

    /**
     * Get detailed report statistics drill-down data.
     */
    private function getReportStatisticsDrillDown(Carbon $startDate, Carbon $endDate): array
    {
        $reportStats = $this->analyticsService->getReportStatistics($startDate, $endDate);
        
        return [
            'total_reports' => $reportStats['total_reports'] ?? 0,
            'by_status' => $reportStats['by_status'] ?? [],
            'by_type' => $reportStats['by_type'] ?? [],
            'avg_resolution_time' => $reportStats['avg_resolution_time_hours'] ?? 0,
            'locations_with_most_reports' => $reportStats['locations_with_most_reports'] ?? [],
        ];
    }

    /**
     * Get geographic distribution with zone filtering.
     * Provides zone-based activity display with color-coding by performance.
     *
     * @param array $filters Optional filters (date_range, zone)
     * @return array
     */
    public function getGeographicDistribution(array $filters = []): array
    {
        try {
            $dateRange = $this->parseDateRange($filters);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];

            // Get base geographic distribution from analytics service
            $geoData = $this->analyticsService->getGeographicDistribution($startDate, $endDate);

            // Apply zone filter if specified
            if (isset($filters['zone']) && $filters['zone']) {
                $geoData['collections_by_zone'] = array_filter(
                    $geoData['collections_by_zone'],
                    fn($zoneData) => $zoneData['zone'] === $filters['zone']
                );
                
                $geoData['reports_by_zone'] = array_filter(
                    $geoData['reports_by_zone'],
                    fn($zoneData) => $zoneData['zone'] === $filters['zone']
                );
            }

            // Add color coding based on performance
            $geoData['collections_by_zone'] = array_map(function ($zoneData) {
                $completionRate = $zoneData['completion_rate'] ?? 0;
                
                // Color code: green (>=80%), yellow (60-79%), red (<60%)
                $performanceLevel = match (true) {
                    $completionRate >= 80 => 'high',
                    $completionRate >= 60 => 'medium',
                    default => 'low'
                };
                
                $color = match ($performanceLevel) {
                    'high' => '#22c55e',    // green
                    'medium' => '#eab308',  // yellow
                    'low' => '#ef4444',     // red
                };

                return array_merge($zoneData, [
                    'performance_level' => $performanceLevel,
                    'color' => $color,
                ]);
            }, $geoData['collections_by_zone']);

            // Add activity level for reports
            $geoData['reports_by_zone'] = array_map(function ($zoneData) {
                $totalReports = $zoneData['total_reports'] ?? 0;
                
                // Activity level: high (>10), medium (5-10), low (<5)
                $activityLevel = match (true) {
                    $totalReports > 10 => 'high',
                    $totalReports >= 5 => 'medium',
                    default => 'low'
                };

                return array_merge($zoneData, [
                    'activity_level' => $activityLevel,
                ]);
            }, $geoData['reports_by_zone']);

            return $geoData;
        } catch (\Exception $e) {
            Log::error('Error getting geographic distribution', [
                'error' => $e->getMessage(),
                'filters' => $filters,
            ]);

            return [
                'collections_by_zone' => [],
                'reports_by_zone' => [],
                'zones_without_collections' => [],
                'total_zones' => 0,
                'error' => true,
            ];
        }
    }

    /**
     * Dismiss an alert for the current user.
     *
     * @param string $alertId Alert identifier (e.g., "assignment_123")
     * @return bool
     */
    public function dismissAlert(string $alertId): bool
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return false;
            }

            // Parse alert ID to get category and identifier
            // Format: "{category}_{id}" e.g., "assignment_123", "report_456", "truck_789"
            $parts = explode('_', $alertId, 2);
            
            if (count($parts) !== 2) {
                Log::warning('Invalid alert ID format', ['alert_id' => $alertId]);
                return false;
            }

            $category = match ($parts[0]) {
                'assignment' => 'unassigned_route',
                'report' => 'overdue_report',
                'truck' => 'truck_maintenance',
                default => null
            };

            if (!$category) {
                Log::warning('Unknown alert category', ['alert_id' => $alertId]);
                return false;
            }

            DismissedAlert::dismissAlert($userId, $category, $alertId);

            // Invalidate cache to refresh alerts
            $this->invalidateUserCache(Auth::user());

            return true;
        } catch (\Exception $e) {
            Log::error('Error dismissing alert', [
                'error' => $e->getMessage(),
                'alert_id' => $alertId,
            ]);

            return false;
        }
    }

    /**
     * Dismiss all alerts for the current user.
     *
     * @return bool
     */
    public function dismissAllAlerts(): bool
    {
        try {
            $userId = Auth::id();
            
            if (!$userId) {
                return false;
            }

            DismissedAlert::clearAllForUser($userId);

            // Invalidate cache to refresh alerts
            $this->invalidateUserCache(Auth::user());

            return true;
        } catch (\Exception $e) {
            Log::error('Error dismissing all alerts', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get chart data for AJAX refresh.
     * Formats data specifically for Chart.js consumption.
     * Requirements: 4.1, 4.2, 4.3, 4.4, 5.4, 12.1, 12.2, 16.5
     *
     * @param string $metricType
     * @param array $filters
     * @param User $user
     * @return array
     */
    public function getChartData(string $metricType, array $filters, User $user): array
    {
        try {
            // Determine date range from filters
            [$startDate, $endDate] = $this->parseDateRange($filters);

            // Get chart data based on metric type
            return match ($metricType) {
                'collection_trends' => $this->getCollectionTrendsChartData($startDate, $endDate, $filters),
                'recycling_breakdown' => $this->getRecyclingBreakdownChartData($startDate, $endDate),
                'route_performance' => $this->getRoutePerformanceChartData($startDate, $endDate),
                'cost_trends' => $this->getCostTrendsChartData($startDate, $endDate),
                default => ['labels' => [], 'values' => []],
            };
        } catch (\Exception $e) {
            Log::error('Error getting chart data', [
                'metric_type' => $metricType,
                'error' => $e->getMessage(),
            ]);

            return ['labels' => [], 'values' => []];
        }
    }

    /**
     * Get collection trends chart data.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return array
     */
    private function getCollectionTrendsChartData(Carbon $startDate, Carbon $endDate, array $filters): array
    {
        $collectionMetrics = $this->analyticsService->getCollectionMetrics($startDate, $endDate, $filters);
        
        return [
            'labels' => $collectionMetrics['trend_data']['labels'] ?? [],
            'values' => $collectionMetrics['trend_data']['values'] ?? [],
            'label' => 'Completion Rate (%)',
        ];
    }

    /**
     * Get recycling breakdown chart data.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getRecyclingBreakdownChartData(Carbon $startDate, Carbon $endDate): array
    {
        $recyclingMetrics = $this->analyticsService->getRecyclingMetrics($startDate, $endDate);
        $breakdown = $recyclingMetrics['material_breakdown'] ?? [];

        $labels = [];
        $values = [];

        foreach ($breakdown as $material) {
            $labels[] = ucfirst($material['material_type']);
            $values[] = $material['weight'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'label' => 'Weight (kg)',
        ];
    }

    /**
     * Get route performance chart data.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getRoutePerformanceChartData(Carbon $startDate, Carbon $endDate): array
    {
        $routeMetrics = $this->analyticsService->getRoutePerformance($startDate, $endDate);
        $routes = $routeMetrics['all_routes'] ?? [];

        // Sort by completion rate and take top 10
        usort($routes, fn($a, $b) => $b['completion_rate'] <=> $a['completion_rate']);
        $topRoutes = array_slice($routes, 0, 10);

        $labels = [];
        $values = [];

        foreach ($topRoutes as $route) {
            $labels[] = $route['route_name'];
            $values[] = $route['completion_rate'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'label' => 'Completion Rate (%)',
        ];
    }

    /**
     * Get cost trends chart data.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function getCostTrendsChartData(Carbon $startDate, Carbon $endDate): array
    {
        $costMetrics = $this->analyticsService->getOperationalCosts($startDate, $endDate);
        
        // If cost data is not available, return empty
        if (!isset($costMetrics['trend_data'])) {
            return ['labels' => [], 'values' => []];
        }

        return [
            'labels' => $costMetrics['trend_data']['labels'] ?? [],
            'values' => $costMetrics['trend_data']['values'] ?? [],
            'label' => 'Cost ($)',
        ];
    }
}
