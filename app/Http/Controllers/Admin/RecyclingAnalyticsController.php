<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RecyclingAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecyclingAnalyticsController extends Controller
{
    protected RecyclingAnalyticsService $analyticsService;

    public function __construct(RecyclingAnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard with key metrics.
     * Requirements: 7.5, 9.3, 12.5, 13.2, 13.3
     */
    public function dashboard(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get key metrics
        $recyclingRate = $this->analyticsService->getRecyclingRate($startDate, $endDate);
        $materialTotals = $this->analyticsService->getMaterialTotals($startDate, $endDate);
        
        // Get weekly trend for last 12 weeks
        $trendStartDate = Carbon::now()->subWeeks(12);
        $trendEndDate = Carbon::now();
        $weeklyTrend = $this->analyticsService->getTrendData($trendStartDate, $trendEndDate, 'weekly');

        // Get target progress for current month
        $currentMonth = Carbon::now();
        $targetProgress = $this->analyticsService->getTargetProgress($currentMonth);

        // Get comparison with previous period
        $comparison = $this->analyticsService->compareWithPreviousPeriod($startDate, $endDate);

        // Transform data for view
        $metrics = [
            'total_weight' => $recyclingRate['total_weight'],
            'total_logs' => $recyclingRate['log_count'],
            'average_per_log' => $recyclingRate['weight_per_log'],
            'recycling_rate' => $recyclingRate['weight_per_zone'],
        ];

        // Transform target progress for view
        $targets = collect($targetProgress)->map(function ($target) {
            return [
                'material_type' => $target['material_type'],
                'target_weight' => $target['target_weight'],
                'current_weight' => $target['actual_weight'],
                'progress' => $target['progress_percentage'],
                'is_achieved' => $target['is_achieved'],
            ];
        })->toArray();

        return view('admin.recycling.analytics.dashboard', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => $metrics,
            'materialTotals' => $materialTotals,
            'weeklyTrend' => $weeklyTrend,
            'targets' => $targets,
            'comparison' => $comparison
        ]);
    }

    /**
     * Display material breakdown analysis with charts.
     * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 13.4
     */
    public function materialAnalysis(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get material totals
        $materialTotals = $this->analyticsService->getMaterialTotals($startDate, $endDate);

        // Get comparison with previous period
        $comparison = $this->analyticsService->compareWithPreviousPeriod($startDate, $endDate);

        return view('admin.recycling.analytics.materials', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'materialTotals' => $materialTotals,
            'comparison' => $comparison
        ]);
    }

    /**
     * Display zone performance analytics.
     * Requirements: 8.1, 8.2, 8.3, 8.4, 8.5
     */
    public function zonePerformance(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get material type filter
        $materialTypes = $request->input('material_types', []);
        if (!is_array($materialTypes)) {
            $materialTypes = [];
        }

        // Get zone performance data
        $zonePerformance = $this->analyticsService->getZonePerformance($startDate, $endDate, $materialTypes);

        // Available material types for filter
        $availableMaterialTypes = [
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        return view('admin.recycling.analytics.zones', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'zonePerformance' => $zonePerformance,
            'materialTypes' => $materialTypes,
            'availableMaterialTypes' => $availableMaterialTypes
        ]);
    }

    /**
     * Display trend analysis with time-series data.
     * Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 13.5
     */
    public function trendAnalysis(Request $request)
    {
        // Get date range from request or default to last 90 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(90);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get interval from request or default to weekly
        $interval = $request->input('interval', 'weekly');
        if (!in_array($interval, ['daily', 'weekly', 'monthly'])) {
            $interval = 'weekly';
        }

        // Get material type filter
        $materialTypes = $request->input('material_types', []);
        if (!is_array($materialTypes)) {
            $materialTypes = [];
        }

        // Get trend data
        $trendData = $this->analyticsService->getTrendData($startDate, $endDate, $interval, $materialTypes);

        // Available material types for filter
        $availableMaterialTypes = [
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        return view('admin.recycling.analytics.trends', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'interval' => $interval,
            'trendData' => $trendData,
            'materialTypes' => $materialTypes,
            'availableMaterialTypes' => $availableMaterialTypes
        ]);
    }

    /**
     * Display crew performance statistics and rankings.
     * Requirements: 10.1, 10.2, 10.3, 10.4, 10.5
     */
    public function crewPerformance(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get crew performance data
        $crewPerformance = $this->analyticsService->getCrewPerformance($startDate, $endDate);

        return view('admin.recycling.analytics.crew', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'crewPerformance' => $crewPerformance
        ]);
    }
}
