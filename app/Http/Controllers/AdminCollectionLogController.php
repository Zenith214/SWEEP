<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddAdminNoteRequest;
use App\Models\CollectionLog;
use App\Models\Route;
use App\Models\User;
use App\Services\CollectionLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminCollectionLogController extends Controller
{
    protected CollectionLogService $collectionLogService;

    public function __construct(CollectionLogService $collectionLogService)
    {
        $this->collectionLogService = $collectionLogService;
    }

    /**
     * Display all collection logs with filters.
     * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $filters = [
            'start_date' => $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d')),
            'end_date' => $request->input('end_date', Carbon::now()->format('Y-m-d')),
            'status' => $request->input('status', ''),
            'route_id' => $request->input('route_id', ''),
            'user_id' => $request->input('user_id', ''),
            'search' => $request->input('search', ''),
        ];

        // Get collection logs with filters
        $logs = $this->collectionLogService->getLogsWithFilters($filters);

        // Paginate the results
        $perPage = 20;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedLogs = new \Illuminate\Pagination\LengthAwarePaginator(
            $logs->slice($offset, $perPage),
            $logs->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get routes and crew members for filter dropdowns
        $routes = Route::where('is_active', true)->orderBy('name')->get();
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();

        // Get status options
        $statusOptions = [
            CollectionLog::STATUS_COMPLETED => 'Completed',
            CollectionLog::STATUS_INCOMPLETE => 'Incomplete',
            CollectionLog::STATUS_ISSUE_REPORTED => 'Issue Reported',
        ];

        return view('admin.collection-logs.index', [
            'logs' => $paginatedLogs,
            'filters' => $filters,
            'routes' => $routes,
            'crewMembers' => $crewMembers,
            'statusOptions' => $statusOptions,
        ]);
    }

    /**
     * Display detailed collection log view.
     * Requirements: 8.1, 8.2, 8.3, 8.4, 8.5
     */
    public function show(CollectionLog $collectionLog)
    {
        // Load all necessary relationships
        $collectionLog->load([
            'assignment.truck',
            'assignment.route',
            'assignment.user',
            'creator',
            'photos',
            'adminNotes.admin'
        ]);

        return view('admin.collection-logs.show', compact('collectionLog'));
    }

    /**
     * Add an administrative note to a collection log.
     * Requirements: 11.1, 11.2, 11.3, 11.4, 11.5
     */
    public function addNote(AddAdminNoteRequest $request, CollectionLog $collectionLog)
    {
        try {
            // Create the admin note
            $collectionLog->adminNotes()->create([
                'admin_id' => auth()->id(),
                'note' => $request->input('note'),
            ]);

            return redirect()->back()
                ->with('success', 'Admin note added successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add admin note: ' . $e->getMessage());
        }
    }

    /**
     * Display issue analysis view with recurring issues.
     * Requirements: 10.1, 10.2, 10.3, 10.4
     */
    public function issueAnalysis(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get routes with recurring issues (threshold of 2 or more issues)
        $threshold = $request->input('threshold', 2);
        $routesWithIssues = $this->collectionLogService->getRoutesWithRecurringIssues(
            $startDate, 
            $endDate, 
            $threshold
        );

        // Get issue breakdown by type
        $issuesByType = $this->collectionLogService->getIssuesByType($startDate, $endDate);

        return view('admin.collection-logs.issue-analysis', [
            'routesWithIssues' => $routesWithIssues,
            'issuesByType' => $issuesByType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Display all issues for a specific route.
     * Requirements: 10.5
     */
    public function routeIssues(Request $request, Route $route)
    {
        // Get date range from request or default to last 90 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(90);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get all issue logs for this route
        $issueLogs = CollectionLog::withIssues()
            ->forDateRange($startDate, $endDate)
            ->whereHas('assignment', function($q) use ($route) {
                $q->where('route_id', $route->id);
            })
            ->with([
                'assignment.user',
                'assignment.truck',
                'creator',
                'photos',
                'adminNotes.admin'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.collection-logs.route-issues', [
            'route' => $route,
            'issueLogs' => $issueLogs,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
