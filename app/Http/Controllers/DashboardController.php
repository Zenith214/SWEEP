<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use App\Services\DashboardService;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AlertService $alertService;
    protected DashboardService $dashboardService;
    protected ExportService $exportService;

    public function __construct(
        AlertService $alertService,
        DashboardService $dashboardService,
        ExportService $exportService
    ) {
        $this->alertService = $alertService;
        $this->dashboardService = $dashboardService;
        $this->exportService = $exportService;
        
        // Apply performance tracking middleware to all dashboard routes
        $this->middleware('track.dashboard.performance');
    }
    /**
     * Route to role-specific dashboard based on user role.
     * Requirements: 4.1, 4.2, 4.3
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('collection_crew')) {
            return redirect()->route('crew.dashboard');
        }

        if ($user->hasRole('resident')) {
            return redirect()->route('resident.dashboard');
        }

        // Fallback if no role is assigned
        abort(403, 'No role assigned to your account. Please contact an administrator.');
    }

    /**
     * Display the administrator dashboard.
     * Requirements: 4.1, 8.1, 12.1, 12.2, 12.3, 12.4, 12.5, 1.1, 1.2, 1.3, 1.4, 1.5, 10.1, 10.2, 10.3, 10.4, 10.5, 17.4
     */
    public function adminDashboard(Request $request): View
    {
        $user = $request->user();
        
        // Get all assignment alerts
        $allAlerts = $this->alertService->getAssignmentAlerts();
        
        // Filter out dismissed alerts
        $alerts = array_filter($allAlerts, function ($alert) use ($user) {
            return !$this->alertService->isAlertDismissed($alert['type'], $user);
        });
        
        // Get comprehensive metrics for admin dashboard
        $filters = $request->only(['start_date', 'end_date', 'route_id', 'zone_id', 'period', 'compare_period']);
        $metrics = $this->dashboardService->getAdminMetrics($filters);
        
        // Get user preferences for dashboard customization
        $preferences = $this->dashboardService->getUserPreferences($user);
        $metrics['preferences'] = $preferences;
        
        // Get recent generated reports for quick access
        $recentReports = \App\Models\GeneratedReport::with('scheduledReport')
            ->whereHas('scheduledReport', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->limit(5)
            ->get();
        
        return view('dashboards.admin', [
            'user' => $user,
            'alerts' => array_values($alerts), // Re-index array after filtering
            'metrics' => $metrics,
            'recentReports' => $recentReports,
        ]);
    }

    /**
     * Display the collection crew dashboard.
     * Requirements: 4.2, 8.2, 19.1, 19.2, 19.3, 19.4, 19.5
     */
    public function crewDashboard(Request $request): View
    {
        $user = $request->user();
        
        // Get crew-specific metrics
        $metrics = $this->dashboardService->getCrewMetrics($user);
        
        return view('dashboards.crew', [
            'user' => $user,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Display the resident dashboard.
     * Requirements: 4.3, 8.3, 20.1, 20.2, 20.3, 20.4, 20.5
     */
    public function residentDashboard(Request $request): View
    {
        $user = $request->user();
        
        // Get resident-specific metrics
        $metrics = $this->dashboardService->getResidentMetrics($user);
        
        return view('dashboards.resident', [
            'user' => $user,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Dismiss an alert for the current user.
     * Requirements: 12.4, 12.5
     */
    public function dismissAlert(Request $request): RedirectResponse
    {
        $request->validate([
            'alert_type' => 'required|string|in:unassigned_routes,underutilized_trucks',
        ]);

        $this->alertService->dismissAlert($request->input('alert_type'), $request->user());

        return redirect()->back()->with('success', 'Alert dismissed');
    }

    /**
     * Get fresh metrics via AJAX for dynamic updates.
     * Requirements: 1.4, 1.5
     */
    public function getMetrics(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->only(['start_date', 'end_date', 'route_id', 'zone_id']);
        
        $metrics = match (true) {
            $user->hasRole('administrator') => $this->dashboardService->getAdminMetrics($filters),
            $user->hasRole('collection_crew') => $this->dashboardService->getCrewMetrics($user),
            $user->hasRole('resident') => $this->dashboardService->getResidentMetrics($user),
            default => [],
        };
        
        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle PDF and CSV export requests.
     * Requirements: 11.1, 11.2, 11.3, 11.4, 11.5
     */
    public function export(Request $request): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'format' => 'required|string|in:pdf,csv',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|string|in:7days,30days,90days,custom',
            'compare_period' => 'nullable|string|in:previous_week,previous_month,previous_quarter,previous_year',
        ]);
        
        $user = $request->user();
        $format = $request->input('format');
        $filters = $request->only(['start_date', 'end_date', 'route_id', 'zone_id', 'period', 'compare_period']);
        
        // Get metrics based on user role
        $metrics = match (true) {
            $user->hasRole('administrator') => $this->dashboardService->getAdminMetrics($filters),
            $user->hasRole('collection_crew') => $this->dashboardService->getCrewMetrics($user),
            $user->hasRole('resident') => $this->dashboardService->getResidentMetrics($user),
            default => [],
        };
        
        // Get user preferences for visible widgets
        $preferences = $this->dashboardService->getUserPreferences($user);
        
        // Generate title with period information
        $title = 'Dashboard Report';
        if (isset($metrics['metadata']['period_start']) && isset($metrics['metadata']['period_end'])) {
            $title .= ' - ' . $metrics['metadata']['period_start'] . ' to ' . $metrics['metadata']['period_end'];
        }
        
        try {
            if ($format === 'pdf') {
                $filePath = $this->exportService->exportToPDF($metrics, $preferences, $title);
                $filename = basename($filePath);
                
                return response()->download($filePath, $filename, [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend(true);
            } else {
                $filePath = $this->exportService->exportToCSV($metrics);
                $filename = basename($filePath);
                
                return response()->download($filePath, $filename, [
                    'Content-Type' => 'text/csv',
                ])->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            \Log::error('Export failed', [
                'user_id' => $user->id,
                'format' => $format,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Export failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Persist user dashboard customizations.
     * Requirements: 9.1, 9.2, 9.3, 9.4, 9.5
     */
    public function savePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'widget_visibility' => 'nullable|array',
            'widget_order' => 'nullable|array',
            'default_filters' => 'nullable|array',
            'default_view' => 'nullable|string',
        ]);
        
        $user = $request->user();
        $preferences = $request->only(['widget_visibility', 'widget_order', 'default_filters', 'default_view']);
        
        try {
            $success = $this->dashboardService->saveUserPreferences($user, $preferences);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Preferences saved successfully' : 'Failed to save preferences',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save dashboard preferences', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences',
            ], 500);
        }
    }

    /**
     * Provide detailed data for specific metrics (drill-down).
     * Requirements: 15.1, 15.2, 15.3, 15.4, 15.5
     */
    public function drillDown(Request $request, string $metric): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'route_id' => 'nullable|integer|exists:routes,id',
            'zone_id' => 'nullable|integer|exists:zones,id',
        ]);
        
        $user = $request->user();
        $filters = $request->only(['start_date', 'end_date', 'route_id', 'zone_id']);
        
        try {
            // Get detailed data for the specific metric
            $detailData = $this->dashboardService->getDrillDownData($metric, $filters, $user);
            
            return response()->json([
                'success' => true,
                'metric' => $metric,
                'data' => $detailData,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            \Log::error('Drill-down failed', [
                'user_id' => $user->id,
                'metric' => $metric,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load detailed data',
            ], 500);
        }
    }

    /**
     * Dismiss a specific alert for the current user.
     * Requirements: 14.4
     */
    public function dismissAlertById(Request $request, string $alertId): JsonResponse
    {
        try {
            $success = $this->dashboardService->dismissAlert($alertId);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Alert dismissed successfully' : 'Failed to dismiss alert',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to dismiss alert', [
                'user_id' => $request->user()->id,
                'alert_id' => $alertId,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss alert',
            ], 500);
        }
    }

    /**
     * Dismiss all alerts for the current user.
     * Requirements: 14.4
     */
    public function dismissAllAlertsAction(Request $request): JsonResponse
    {
        try {
            $success = $this->dashboardService->dismissAllAlerts();
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'All alerts dismissed successfully' : 'Failed to dismiss alerts',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to dismiss all alerts', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss alerts',
            ], 500);
        }
    }

    /**
     * Get geographic distribution with zone filtering.
     * Requirements: 18.1, 18.2, 18.3, 18.4, 18.5
     */
    public function geographicDistribution(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'zone' => 'nullable|string',
        ]);
        
        $filters = $request->only(['start_date', 'end_date', 'zone']);
        
        try {
            $geoData = $this->dashboardService->getGeographicDistribution($filters);
            
            return response()->json([
                'success' => true,
                'data' => $geoData,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get geographic distribution', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load geographic distribution',
            ], 500);
        }
    }

    /**
     * Get chart data for AJAX refresh.
     * Requirements: 4.1, 4.2, 4.3, 4.4, 5.4, 12.1, 12.2, 16.5
     */
    public function getChartData(Request $request, string $metricType): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|integer|in:7,30,90',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'route_id' => 'nullable|integer|exists:routes,id',
            'zone' => 'nullable|string',
        ]);
        
        $user = $request->user();
        $filters = $request->only(['period', 'start_date', 'end_date', 'route_id', 'zone']);
        
        try {
            $chartData = $this->dashboardService->getChartData($metricType, $filters, $user);
            
            return response()->json([
                'success' => true,
                'labels' => $chartData['labels'] ?? [],
                'values' => $chartData['values'] ?? [],
                'metric_type' => $metricType,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get chart data', [
                'user_id' => $user->id,
                'metric_type' => $metricType,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load chart data',
                'labels' => [],
                'values' => [],
            ], 500);
        }
    }
}
