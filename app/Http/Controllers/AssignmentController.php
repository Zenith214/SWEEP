<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Requests\CopyAssignmentsRequest;
use App\Models\Assignment;
use App\Models\Truck;
use App\Models\User;
use App\Models\Route;
use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    protected AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display the assignment calendar interface.
     */
    public function index(Request $request)
    {
        // Get filter options
        $trucks = Truck::orderBy('truck_number')->get();
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();

        return view('admin.assignments.index', compact('trucks', 'crewMembers'));
    }

    /**
     * Get calendar data for AJAX requests.
     */
    public function getCalendarData(Request $request)
    {
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));

        // Get assignments in the date range
        $assignments = $this->assignmentService->getAssignmentsInRange($start, $end);

        // Apply filters if provided
        if ($request->has('truck_id') && $request->input('truck_id') !== '') {
            $assignments = $assignments->where('truck_id', $request->input('truck_id'));
        }

        if ($request->has('user_id') && $request->input('user_id') !== '') {
            $assignments = $assignments->where('user_id', $request->input('user_id'));
        }

        // Format data for FullCalendar
        $events = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->id,
                'title' => $assignment->truck->truck_number . ' - ' . $assignment->route->name,
                'start' => $assignment->assignment_date->format('Y-m-d'),
                'url' => route('admin.assignments.show', $assignment),
                'backgroundColor' => $this->getEventColor($assignment),
                'borderColor' => $this->getEventColor($assignment),
                'extendedProps' => [
                    'truck_number' => $assignment->truck->truck_number,
                    'route_name' => $assignment->route->name,
                    'crew_name' => $assignment->user->name,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Get event color based on truck or route.
     */
    protected function getEventColor(Assignment $assignment): string
    {
        // Generate a consistent color based on truck_id
        $colors = ['#0d9488', '#0891b2', '#0284c7', '#2563eb', '#4f46e5', '#7c3aed', '#c026d3', '#db2777'];
        return $colors[$assignment->truck_id % count($colors)];
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create(Request $request)
    {
        // Get operational trucks
        $trucks = Truck::where('operational_status', Truck::STATUS_OPERATIONAL)
            ->orderBy('truck_number')
            ->get();

        // Get collection crew members
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();

        // Get active routes
        $routes = Route::where('is_active', true)->orderBy('name')->get();

        // Pre-fill date if provided
        $selectedDate = $request->input('date', now()->format('Y-m-d'));

        // Pre-fill route if provided
        $selectedRouteId = $request->input('route_id');

        return view('admin.assignments.create', compact(
            'trucks',
            'crewMembers',
            'routes',
            'selectedDate',
            'selectedRouteId'
        ));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(StoreAssignmentRequest $request)
    {
        try {
            $assignment = $this->assignmentService->createAssignment($request->validated());

            return redirect()
                ->route('admin.assignments.index')
                ->with('success', 'Assignment created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment)
    {
        $assignment->load(['truck', 'user', 'route']);

        return view('admin.assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit(Assignment $assignment)
    {
        // Get operational trucks
        $trucks = Truck::where('operational_status', Truck::STATUS_OPERATIONAL)
            ->orderBy('truck_number')
            ->get();

        // Get collection crew members
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();

        // Get active routes
        $routes = Route::where('is_active', true)->orderBy('name')->get();

        $assignment->load(['truck', 'user', 'route']);

        return view('admin.assignments.edit', compact(
            'assignment',
            'trucks',
            'crewMembers',
            'routes'
        ));
    }

    /**
     * Update the specified assignment in storage.
     */
    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        try {
            $this->assignmentService->updateAssignment($assignment, $request->validated());

            return redirect()
                ->route('admin.assignments.show', $assignment)
                ->with('success', 'Assignment updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel the specified assignment.
     */
    public function cancel(Request $request, Assignment $assignment)
    {
        // Validate cancellation reason if provided
        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->assignmentService->cancelAssignment(
                $assignment,
                $request->input('cancellation_reason')
            );

            return redirect()
                ->route('admin.assignments.index')
                ->with('success', 'Assignment cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for copying assignments.
     */
    public function copyForm(Request $request)
    {
        // Get all trucks for optional filtering
        $trucks = Truck::orderBy('truck_number')->get();

        // Pre-fill source date if provided
        $sourceDate = $request->input('source_date', now()->format('Y-m-d'));

        // Get assignments for the source date to show preview
        $sourceAssignments = collect();
        if ($sourceDate) {
            $sourceAssignments = $this->assignmentService->getAssignmentsForDate(
                Carbon::parse($sourceDate)
            );
        }

        return view('admin.assignments.copy', compact(
            'trucks',
            'sourceDate',
            'sourceAssignments'
        ));
    }

    /**
     * Copy assignments from one date to another.
     */
    public function copy(CopyAssignmentsRequest $request)
    {
        try {
            $sourceDate = Carbon::parse($request->input('source_date'));
            $targetDate = Carbon::parse($request->input('target_date'));
            
            $filters = [];
            if ($request->has('truck_ids') && !empty($request->input('truck_ids'))) {
                $filters['truck_ids'] = $request->input('truck_ids');
            }

            $results = $this->assignmentService->copyAssignments(
                $sourceDate,
                $targetDate,
                $filters
            );

            $successCount = count($results['success']);
            $conflictCount = count($results['conflicts']);

            if ($successCount > 0 && $conflictCount === 0) {
                return redirect()
                    ->route('admin.assignments.index')
                    ->with('success', "Successfully copied {$successCount} assignment(s).");
            } elseif ($successCount > 0 && $conflictCount > 0) {
                return redirect()
                    ->route('admin.assignments.index')
                    ->with('warning', "Copied {$successCount} assignment(s). {$conflictCount} assignment(s) could not be copied due to conflicts.")
                    ->with('conflicts', $results['conflicts']);
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'No assignments could be copied due to conflicts.')
                    ->with('conflicts', $results['conflicts']);
            }
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display routes without assignments.
     */
    public function unassignedRoutes(Request $request)
    {
        // Default to next 7 days
        $startDate = $request->has('start_date') 
            ? Carbon::parse($request->input('start_date'))
            : now()->startOfDay();
        
        $endDate = $request->has('end_date')
            ? Carbon::parse($request->input('end_date'))
            : now()->addDays(7)->endOfDay();

        // Get unassigned routes
        $unassignedRoutes = $this->assignmentService->getUnassignedRoutes($startDate, $endDate);

        return view('admin.assignments.unassigned-routes', compact(
            'unassignedRoutes',
            'startDate',
            'endDate'
        ));
    }
}
