<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\CollectionLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionAnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected CollectionLogService $collectionLogService;

    public function __construct(AnalyticsService $analyticsService, CollectionLogService $collectionLogService)
    {
        $this->analyticsService = $analyticsService;
        $this->collectionLogService = $collectionLogService;
    }

    /**
     * Display collection analytics dashboard.
     */
    public function index(Request $request)
    {
        // Default to last 30 days
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get key metrics
        $totalCollections = \App\Models\CollectionLog::forDateRange($start, $end)->count();
        $completionRate = $this->collectionLogService->getCompletionRate($start, $end);
        $avgCompletionTimeMinutes = $this->analyticsService->getAverageCompletionTime($start, $end);
        $avgCompletionTime = $avgCompletionTimeMinutes > 0 ? round($avgCompletionTimeMinutes / 60, 1) : 0;
        $issueCount = \App\Models\CollectionLog::withIssues()->forDateRange($start, $end)->count();

        return view('admin.analytics.collections.index', compact(
            'startDate',
            'endDate',
            'totalCollections',
            'completionRate',
            'avgCompletionTime',
            'issueCount'
        ));
    }

    /**
     * Get completion rates data for chart (AJAX endpoint).
     */
    public function getCompletionRates(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $trend = $this->analyticsService->getCompletionTrend($start, $end);

        return response()->json([
            'success' => true,
            'data' => $trend
        ]);
    }

    /**
     * Get status breakdown data for pie chart (AJAX endpoint).
     */
    public function getStatusBreakdown(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $breakdown = $this->collectionLogService->getStatusBreakdown($start, $end);

        return response()->json([
            'success' => true,
            'data' => $breakdown
        ]);
    }

    /**
     * Get crew performance data (AJAX endpoint).
     */
    public function getCrewPerformance(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $performance = $this->analyticsService->getCrewPerformance($start, $end);

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }

    /**
     * Get route performance data (AJAX endpoint).
     */
    public function getRoutePerformance(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $performance = $this->analyticsService->getRoutePerformance($start, $end);

        return response()->json([
            'success' => true,
            'data' => $performance
        ]);
    }
}
