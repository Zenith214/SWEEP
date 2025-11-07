<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Requests\DuplicateScheduleRequest;
use App\Models\Schedule;
use App\Models\Route;
use App\Models\ScheduleDay;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * The schedule service instance.
     */
    protected ScheduleService $scheduleService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of schedules with route information and filters.
     */
    public function index(Request $request): View
    {
        $query = Schedule::with(['route', 'scheduleDays']);

        // Filter by route
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->input('route_id'));
        }

        // Filter by active/inactive status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Pagination
        $schedules = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();

        // Get all routes for filter dropdown
        $routes = Route::orderBy('name')->get();

        return view('admin.schedules.index', compact('schedules', 'routes'));
    }

    /**
     * Show the form for creating a new schedule.
     */
    public function create(): View
    {
        $routes = Route::where('is_active', true)->orderBy('name')->get();
        $days = ScheduleDay::DAYS;

        return view('admin.schedules.create', compact('routes', 'days'));
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        try {
            $schedule = $this->scheduleService->createSchedule($request->validated());

            // Check for conflicts (exclude the newly created schedule itself)
            if ($this->scheduleService->checkConflicts($schedule, $schedule)) {
                $schedule->delete();
                return back()
                    ->withInput()
                    ->with('error', 'This schedule conflicts with an existing schedule on the same route.');
            }

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule created successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified schedule with details.
     */
    public function show(Schedule $schedule): View
    {
        $schedule->load(['route', 'scheduleDays']);

        return view('admin.schedules.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified schedule.
     */
    public function edit(Schedule $schedule): View
    {
        $schedule->load(['route', 'scheduleDays']);
        $routes = Route::where('is_active', true)->orderBy('name')->get();
        $days = ScheduleDay::DAYS;
        $selectedDays = $schedule->getDaysOfWeek();

        return view('admin.schedules.edit', compact('schedule', 'routes', 'days', 'selectedDays'));
    }

    /**
     * Update the specified schedule in storage with conflict checking.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        try {
            $this->scheduleService->updateSchedule($schedule, $request->validated());

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule updated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the specified schedule.
     */
    public function toggleActive(Schedule $schedule): JsonResponse
    {
        $schedule->is_active = !$schedule->is_active;
        $schedule->save();

        return response()->json([
            'success' => true,
            'is_active' => $schedule->is_active,
            'message' => $schedule->is_active 
                ? 'Schedule activated successfully.' 
                : 'Schedule deactivated successfully.',
        ]);
    }

    /**
     * Remove the specified schedule from storage (soft delete).
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    /**
     * Show the form for duplicating a schedule to another route.
     */
    public function duplicate(Schedule $schedule): View
    {
        $schedule->load(['route', 'scheduleDays']);
        
        // Get all routes except the current one
        $routes = Route::where('is_active', true)
            ->where('id', '!=', $schedule->route_id)
            ->orderBy('name')
            ->get();

        return view('admin.schedules.duplicate', compact('schedule', 'routes'));
    }

    /**
     * Store the duplicated schedule for a different route.
     */
    public function storeDuplicate(DuplicateScheduleRequest $request, Schedule $schedule): RedirectResponse
    {
        try {
            $targetRoute = Route::findOrFail($request->input('target_route_id'));
            
            $newSchedule = $this->scheduleService->duplicateSchedule($schedule, $targetRoute);

            return redirect()->route('admin.schedules.index')
                ->with('success', 'Schedule duplicated successfully to route: ' . $targetRoute->name);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
