<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecyclingLogRequest;
use App\Models\Assignment;
use App\Models\RecyclingLog;
use App\Models\RecyclingLogMaterial;
use App\Services\RecyclingAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecyclingLogController extends Controller
{
    /**
     * The recycling analytics service instance.
     *
     * @var RecyclingAnalyticsService
     */
    protected $analyticsService;

    /**
     * Create a new controller instance.
     *
     * @param RecyclingAnalyticsService $analyticsService
     * @return void
     */
    public function __construct(RecyclingAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display crew member's recycling logs with date filtering.
     * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date') 
            ? Carbon::parse($request->input('start_date')) 
            : Carbon::now()->subDays(30);
        
        $endDate = $request->input('end_date') 
            ? Carbon::parse($request->input('end_date')) 
            : Carbon::now();

        // Get recycling logs for the current user
        $logs = RecyclingLog::with(['route', 'materials'])
            ->forUser($user)
            ->forDateRange($startDate, $endDate)
            ->recent()
            ->paginate(20);

        return view('crew.recycling-logs.index', [
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Show form to create a new recycling log.
     * Requirements: 1.1, 1.2, 1.5, 2.1, 2.2, 3.1, 3.5, 4.1, 15.1
     */
    public function create()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Get today's active assignment for the current user
        $assignment = Assignment::with(['truck', 'route'])
            ->where('user_id', $user->id)
            ->forDate($today)
            ->active()
            ->first();

        // Material types available
        $materialTypes = [
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        return view('crew.recycling-logs.form', [
            'assignment' => $assignment,
            'materialTypes' => $materialTypes,
            'log' => null
        ]);
    }

    /**
     * Store a new recycling log with materials.
     * Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3
     */
    public function store(RecyclingLogRequest $request)
    {
        $user = auth()->user();

        try {
            DB::beginTransaction();

            // Get the active assignment if exists
            $assignment = null;
            $routeId = null;
            
            if ($request->filled('assignment_id')) {
                $assignment = Assignment::find($request->assignment_id);
                if ($assignment && $assignment->user_id === $user->id && $assignment->isActive()) {
                    $routeId = $assignment->route_id;
                }
            }

            // Create the recycling log
            $log = RecyclingLog::create([
                'user_id' => $user->id,
                'assignment_id' => $assignment?->id,
                'route_id' => $routeId,
                'collection_date' => $request->collection_date,
                'notes' => $request->notes,
                'quality_issue' => $request->boolean('quality_issue', false),
            ]);

            // Create material entries
            foreach ($request->materials as $materialData) {
                RecyclingLogMaterial::create([
                    'recycling_log_id' => $log->id,
                    'material_type' => $materialData['material_type'],
                    'weight' => $materialData['weight'],
                ]);
            }

            DB::commit();

            // Invalidate analytics cache after successful creation
            $this->analyticsService->invalidateCache();

            return redirect()->route('crew.recycling-logs.index')
                ->with('success', 'Recycling log created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create recycling log: ' . $e->getMessage());
        }
    }

    /**
     * Show form to edit a recycling log (within edit window).
     * Requirements: 14.1, 14.2, 14.3
     */
    public function edit(RecyclingLog $recyclingLog)
    {
        // Authorize the update action
        $this->authorize('update', $recyclingLog);

        $user = auth()->user();

        // Load relationships
        $recyclingLog->load(['route', 'materials', 'assignment']);

        // Material types available
        $materialTypes = [
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        // Calculate remaining edit time
        $editTimeRemaining = 120 - $recyclingLog->created_at->diffInMinutes(now());

        return view('crew.recycling-logs.form', [
            'log' => $recyclingLog,
            'assignment' => $recyclingLog->assignment,
            'materialTypes' => $materialTypes,
            'editTimeRemaining' => $editTimeRemaining
        ]);
    }

    /**
     * Update a recycling log (within edit window).
     * Requirements: 14.1, 14.2, 14.3, 14.4, 14.5
     */
    public function update(RecyclingLogRequest $request, RecyclingLog $recyclingLog)
    {
        // Authorize the update action
        $this->authorize('update', $recyclingLog);

        try {
            DB::beginTransaction();

            // Update the recycling log
            $recyclingLog->update([
                'collection_date' => $request->collection_date,
                'notes' => $request->notes,
                'quality_issue' => $request->boolean('quality_issue', false),
            ]);

            // Delete existing materials
            $recyclingLog->materials()->delete();

            // Create new material entries
            foreach ($request->materials as $materialData) {
                RecyclingLogMaterial::create([
                    'recycling_log_id' => $recyclingLog->id,
                    'material_type' => $materialData['material_type'],
                    'weight' => $materialData['weight'],
                ]);
            }

            DB::commit();

            // Invalidate analytics cache after successful update
            $this->analyticsService->invalidateCache();

            return redirect()->route('crew.recycling-logs.index')
                ->with('success', 'Recycling log updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update recycling log: ' . $e->getMessage());
        }
    }
}
