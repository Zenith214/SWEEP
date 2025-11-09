<?php

namespace App\Services;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ReportAnalyticsService
{
    /**
     * Get reports grouped by location for a date range.
     */
    public function getReportsByLocation(Carbon $start, Carbon $end): Collection
    {
        return Report::select('location', DB::raw('COUNT(*) as count'))
            ->forDateRange($start, $end)
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get report counts and percentages by type for a date range.
     */
    public function getReportsByType(Carbon $start, Carbon $end): array
    {
        $reports = Report::select('report_type', DB::raw('COUNT(*) as count'))
            ->forDateRange($start, $end)
            ->groupBy('report_type')
            ->get();

        $total = $reports->sum('count');
        
        $result = [];
        foreach ($reports as $report) {
            $result[] = [
                'type' => $report->report_type,
                'type_label' => Report::REPORT_TYPES[$report->report_type] ?? $report->report_type,
                'count' => $report->count,
                'percentage' => $total > 0 ? round(($report->count / $total) * 100, 2) : 0
            ];
        }

        return $result;
    }

    /**
     * Calculate average resolution time in hours for a date range.
     */
    public function getAverageResolutionTime(Carbon $start, Carbon $end): float
    {
        $resolvedReports = Report::forDateRange($start, $end)
            ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])
            ->whereNotNull('resolved_at')
            ->get();

        if ($resolvedReports->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($resolvedReports as $report) {
            $totalHours += $report->getResolutionTime() ?? 0;
        }

        return round($totalHours / $resolvedReports->count(), 2);
    }

    /**
     * Get average resolution time breakdown by report type.
     */
    public function getResolutionTimeByType(Carbon $start, Carbon $end): array
    {
        $reports = Report::forDateRange($start, $end)
            ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])
            ->whereNotNull('resolved_at')
            ->get()
            ->groupBy('report_type');

        $result = [];
        foreach ($reports as $type => $typeReports) {
            $totalHours = 0;
            foreach ($typeReports as $report) {
                $totalHours += $report->getResolutionTime() ?? 0;
            }

            $result[] = [
                'type' => $type,
                'type_label' => Report::REPORT_TYPES[$type] ?? $type,
                'average_hours' => $typeReports->count() > 0 ? round($totalHours / $typeReports->count(), 2) : 0,
                'count' => $typeReports->count()
            ];
        }

        return $result;
    }

    /**
     * Get reports that exceed the target resolution time.
     */
    public function getOverdueReports(int $targetHours = 48): Collection
    {
        $targetTime = now()->subHours($targetHours);

        return Report::where('status', '!=', Report::STATUS_RESOLVED)
            ->where('status', '!=', Report::STATUS_CLOSED)
            ->where('created_at', '<', $targetTime)
            ->with(['resident', 'route', 'assignedTo'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get type distribution data for charts.
     */
    public function getTypeDistribution(Carbon $start, Carbon $end): array
    {
        $reports = Report::select('report_type', DB::raw('COUNT(*) as count'))
            ->forDateRange($start, $end)
            ->groupBy('report_type')
            ->get();

        $labels = [];
        $data = [];
        
        foreach ($reports as $report) {
            $labels[] = Report::REPORT_TYPES[$report->report_type] ?? $report->report_type;
            $data[] = $report->count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get status distribution data for charts.
     */
    public function getStatusDistribution(Carbon $start, Carbon $end): array
    {
        $reports = Report::select('status', DB::raw('COUNT(*) as count'))
            ->forDateRange($start, $end)
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        
        foreach ($reports as $report) {
            $labels[] = Report::STATUSES[$report->status] ?? $report->status;
            $data[] = $report->count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Identify location hotspots (locations with multiple reports).
     */
    public function getLocationHotspots(Carbon $start, Carbon $end, int $threshold = 3): Collection
    {
        return Report::select('location', DB::raw('COUNT(*) as count'))
            ->forDateRange($start, $end)
            ->groupBy('location')
            ->having('count', '>=', $threshold)
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get daily report submission trend for a date range.
     */
    public function getReportTrend(Carbon $start, Carbon $end): array
    {
        $reports = Report::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->forDateRange($start, $end)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $labels = [];
        $data = [];
        
        foreach ($reports as $report) {
            $labels[] = Carbon::parse($report->date)->format('M d');
            $data[] = $report->count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Calculate key performance indicators for a date range.
     */
    public function getPerformanceMetrics(Carbon $start, Carbon $end): array
    {
        $totalReports = Report::forDateRange($start, $end)->count();
        $pendingReports = Report::forDateRange($start, $end)->pending()->count();
        $resolvedReports = Report::forDateRange($start, $end)
            ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])
            ->count();

        $resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100, 2) : 0;
        $averageResolutionTime = $this->getAverageResolutionTime($start, $end);

        return [
            'total_reports' => $totalReports,
            'pending_reports' => $pendingReports,
            'resolved_reports' => $resolvedReports,
            'resolution_rate' => $resolutionRate,
            'average_resolution_time' => $averageResolutionTime
        ];
    }

    /**
     * Get average resolution time trend over time.
     */
    public function getResolutionTimeTrend(Carbon $start, Carbon $end): array
    {
        // Group by week for better visualization
        $reports = Report::select(
                DB::raw('YEARWEEK(created_at) as week'),
                DB::raw('MIN(created_at) as week_start')
            )
            ->forDateRange($start, $end)
            ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])
            ->whereNotNull('resolved_at')
            ->groupBy('week')
            ->orderBy('week', 'asc')
            ->get();

        $labels = [];
        $data = [];

        foreach ($reports as $weekData) {
            $weekStart = Carbon::parse($weekData->week_start);
            $weekEnd = $weekStart->copy()->addWeek();

            // Get reports for this week
            $weekReports = Report::forDateRange($weekStart, $weekEnd)
                ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_CLOSED])
                ->whereNotNull('resolved_at')
                ->get();

            if ($weekReports->isNotEmpty()) {
                $totalHours = 0;
                foreach ($weekReports as $report) {
                    $totalHours += $report->getResolutionTime() ?? 0;
                }

                $labels[] = $weekStart->format('M d');
                $data[] = round($totalHours / $weekReports->count(), 2);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
