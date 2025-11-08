<?php

namespace App\Services;

use App\Models\CollectionLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get daily completion rates for trend chart.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getCompletionTrend(Carbon $start, Carbon $end): array
    {
        // Get all logs in the date range
        $logs = CollectionLog::forDateRange($start, $end)
            ->with('assignment')
            ->get();

        // Group logs by date
        $logsByDate = $logs->groupBy(function ($log) {
            return $log->assignment->assignment_date->format('Y-m-d');
        });

        // Create array for each day in the range
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
    }

    /**
     * Get performance metrics per crew member.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getCrewPerformance(Carbon $start, Carbon $end): Collection
    {
        $logs = CollectionLog::forDateRange($start, $end)
            ->with(['creator', 'assignment'])
            ->get();

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
                    'user' => $crewLogs->first()->creator,
                    'user_id' => $userId,
                    'user_name' => $crewLogs->first()->creator->name,
                    'total_collections' => $totalLogs,
                    'completed' => $completedLogs,
                    'incomplete' => $incompleteLogs,
                    'issues_reported' => $issueLogs,
                    'completion_rate' => $completionRate,
                    'avg_completion_time_minutes' => $avgCompletionTime,
                ];
            })
            ->sortByDesc('completion_rate')
            ->values();

        return $crewPerformance;
    }

    /**
     * Get performance metrics per route.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getRoutePerformance(Carbon $start, Carbon $end): Collection
    {
        $logs = CollectionLog::forDateRange($start, $end)
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

                return [
                    'route' => $routeLogs->first()->assignment->route,
                    'route_id' => $routeId,
                    'route_name' => $routeLogs->first()->assignment->route->name,
                    'total_collections' => $totalLogs,
                    'completed' => $completedLogs,
                    'incomplete' => $incompleteLogs,
                    'issues_reported' => $issueLogs,
                    'completion_rate' => $completionRate,
                    'avg_completion_time_minutes' => $avgCompletionTime,
                ];
            })
            ->sortByDesc('issues_reported')
            ->values();

        return $routePerformance;
    }

    /**
     * Calculate average time to complete collections.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return float
     */
    public function getAverageCompletionTime(Carbon $start, Carbon $end): float
    {
        $completedLogs = CollectionLog::forDateRange($start, $end)
            ->completed()
            ->whereNotNull('completion_time')
            ->with('assignment')
            ->get();

        if ($completedLogs->isEmpty()) {
            return 0.0;
        }

        $totalMinutes = $completedLogs->sum(function ($log) {
            return $log->assignment->assignment_date->diffInMinutes($log->completion_time);
        });

        return round($totalMinutes / $completedLogs->count(), 2);
    }

    /**
     * Identify routes/zones with most issues.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getIssueHotspots(Carbon $start, Carbon $end): array
    {
        $issueLogs = CollectionLog::withIssues()
            ->forDateRange($start, $end)
            ->with(['assignment.route'])
            ->get();

        // Group by route
        $hotspots = $issueLogs->groupBy('assignment.route_id')
            ->map(function ($logs, $routeId) {
                $route = $logs->first()->assignment->route;
                
                // Group issues by type for this route
                $issuesByType = $logs->groupBy('issue_type')
                    ->map(function ($typeLogs, $issueType) {
                        return [
                            'type' => $issueType,
                            'label' => CollectionLog::ISSUE_TYPES[$issueType] ?? $issueType,
                            'count' => $typeLogs->count(),
                        ];
                    })
                    ->sortByDesc('count')
                    ->values()
                    ->toArray();

                return [
                    'route_id' => $routeId,
                    'route_name' => $route->name,
                    'route' => $route,
                    'total_issues' => $logs->count(),
                    'issues_by_type' => $issuesByType,
                    'most_common_issue' => $issuesByType[0] ?? null,
                ];
            })
            ->sortByDesc('total_issues')
            ->values()
            ->toArray();

        return $hotspots;
    }
}
