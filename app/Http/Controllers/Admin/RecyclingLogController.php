<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecyclingLog;
use App\Models\Route;
use App\Models\User;
use App\Services\RecyclingExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RecyclingLogController extends Controller
{
    protected RecyclingExportService $exportService;

    public function __construct(RecyclingExportService $exportService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->exportService = $exportService;
    }

    /**
     * Display all recycling logs with advanced filtering.
     * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 15.2
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Build query with filters
        $query = RecyclingLog::with(['user', 'route', 'materials'])
            ->forDateRange($startDate, $endDate);

        // Filter by material types
        if ($request->filled('material_types') && is_array($request->material_types)) {
            $query->whereHas('materials', function ($q) use ($request) {
                $q->whereIn('material_type', $request->material_types);
            });
        }

        // Filter by route
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        // Filter by zone
        if ($request->filled('zone')) {
            $query->forZone($request->zone);
        }

        // Filter by crew member
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by quality issues
        if ($request->boolean('quality_issues_only')) {
            $query->withQualityIssues();
        }

        // Get paginated results
        $logs = $query->recent()->paginate(50)->withQueryString();

        // Get filter options
        $routes = Route::orderBy('name')->get();
        $zones = Route::distinct()->orderBy('zone')->pluck('zone');
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();
        $materialTypes = [
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        return view('admin.recycling-logs.index', [
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'routes' => $routes,
            'zones' => $zones,
            'crewMembers' => $crewMembers,
            'materialTypes' => $materialTypes,
            'filters' => $request->only(['material_types', 'route_id', 'zone', 'user_id', 'quality_issues_only'])
        ]);
    }

    /**
     * Display detailed log view with edit history.
     * Requirements: 6.5, 14.4, 14.5, 15.2
     */
    public function show(RecyclingLog $recyclingLog)
    {
        // Authorize the view action
        $this->authorize('view', $recyclingLog);

        // Load relationships
        $recyclingLog->load(['user', 'route', 'assignment', 'materials']);

        return view('admin.recycling-logs.show', [
            'log' => $recyclingLog
        ]);
    }

    /**
     * Export filtered recycling logs to CSV.
     * Requirements: 11.1, 11.4
     */
    public function export(Request $request)
    {
        // Authorize export action (administrators only)
        // Note: This is also enforced by middleware, but explicit check for clarity
        if (!auth()->user()->hasRole('administrator')) {
            abort(403, 'Unauthorized action.');
        }

        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Build query with same filters as index
        $query = RecyclingLog::with(['user', 'route', 'materials'])
            ->forDateRange($startDate, $endDate);

        // Apply filters
        if ($request->filled('material_types') && is_array($request->material_types)) {
            $query->whereHas('materials', function ($q) use ($request) {
                $q->whereIn('material_type', $request->material_types);
            });
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('zone')) {
            $query->forZone($request->zone);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->boolean('quality_issues_only')) {
            $query->withQualityIssues();
        }

        // Get all matching logs (no pagination for export)
        $logs = $query->recent()->get();

        // Generate CSV export
        return $this->exportService->exportLogs(
            $logs,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }
}
