<?php

namespace App\Http\Controllers;

use App\Services\ReportAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportAnalyticsController extends Controller
{
    protected ReportAnalyticsService $analyticsService;

    public function __construct(ReportAnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard.
     */
    public function index(Request $request)
    {
        // Default to last 30 days
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        // Get performance metrics
        $metrics = $this->analyticsService->getPerformanceMetrics($startDate, $endDate);

        // Get overdue reports
        $overdueReports = $this->analyticsService->getOverdueReports(48);

        // Get resolution time by type
        $resolutionTimeByType = $this->analyticsService->getResolutionTimeByType($startDate, $endDate);

        return view('admin.analytics.reports.index', [
            'metrics' => $metrics,
            'overdueReports' => $overdueReports,
            'resolutionTimeByType' => $resolutionTimeByType,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Display location-based analysis view.
     */
    public function locationAnalysis(Request $request)
    {
        // Default to last 30 days
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        // Get reports by location
        $reportsByLocation = $this->analyticsService->getReportsByLocation($startDate, $endDate);

        // Get location hotspots (3+ reports)
        $hotspots = $this->analyticsService->getLocationHotspots($startDate, $endDate, 3);

        return view('admin.analytics.reports.location', [
            'reportsByLocation' => $reportsByLocation,
            'hotspots' => $hotspots,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * Display type-based analysis view.
     */
    public function typeAnalysis(Request $request)
    {
        // Default to last 30 days
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        // Get reports by type
        $reportsByType = $this->analyticsService->getReportsByType($startDate, $endDate);

        // Get resolution time by type
        $resolutionTimeByType = $this->analyticsService->getResolutionTimeByType($startDate, $endDate);

        return view('admin.analytics.reports.type', [
            'reportsByType' => $reportsByType,
            'resolutionTimeByType' => $resolutionTimeByType,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }

    /**
     * API endpoint for type distribution data (AJAX).
     */
    public function getTypeDistribution(Request $request)
    {
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        $distribution = $this->analyticsService->getTypeDistribution($startDate, $endDate);

        return response()->json($distribution);
    }

    /**
     * API endpoint for resolution time data (AJAX).
     */
    public function getResolutionTimes(Request $request)
    {
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        $resolutionTimes = $this->analyticsService->getResolutionTimeTrend($startDate, $endDate);

        return response()->json($resolutionTimes);
    }

    /**
     * API endpoint for status trend data (AJAX).
     */
    public function getStatusTrend(Request $request)
    {
        $startDate = $request->filled('date_from') 
            ? Carbon::parse($request->date_from) 
            : now()->subDays(30);
        
        $endDate = $request->filled('date_to') 
            ? Carbon::parse($request->date_to) 
            : now();

        $statusDistribution = $this->analyticsService->getStatusDistribution($startDate, $endDate);

        return response()->json($statusDistribution);
    }
}
