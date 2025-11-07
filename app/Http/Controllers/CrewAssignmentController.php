<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrewAssignmentController extends Controller
{
    protected AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display today's assignment for the authenticated crew member.
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Get today's assignment for the crew member
        $assignments = $this->assignmentService->getCrewAssignments(
            $user,
            $today,
            $today
        );
        
        $assignment = $assignments->first();
        
        // Get collection time and special instructions from the route's schedule
        $collectionTime = null;
        $specialInstructions = null;
        
        if ($assignment) {
            // Get the active schedule for this route that matches today
            $schedule = $assignment->route->activeSchedules()
                ->whereHas('scheduleDays', function ($query) use ($today) {
                    $query->where('day_of_week', $today->dayOfWeek);
                })
                ->first();
            
            if ($schedule) {
                $collectionTime = $schedule->collection_time;
            }
            
            // Special instructions can come from route notes or assignment notes
            $specialInstructions = $assignment->notes ?? $assignment->route->notes;
        }
        
        return view('crew.assignments.index', compact(
            'assignment',
            'collectionTime',
            'specialInstructions'
        ));
    }

    /**
     * Display upcoming assignments for the next 14 days.
     */
    public function upcoming()
    {
        $user = auth()->user();
        $start = Carbon::today();
        $end = Carbon::today()->addDays(14);
        
        // Get upcoming assignments for the crew member
        $assignments = $this->assignmentService->getCrewAssignments(
            $user,
            $start,
            $end
        );
        
        // Group assignments by date
        $groupedAssignments = $assignments->groupBy(function ($assignment) {
            return $assignment->assignment_date->format('Y-m-d');
        });
        
        // Enhance each assignment with collection time from schedule
        $groupedAssignments = $groupedAssignments->map(function ($dateAssignments) {
            return $dateAssignments->map(function ($assignment) {
                // Get the active schedule for this route that matches the assignment date
                $schedule = $assignment->route->activeSchedules()
                    ->whereHas('scheduleDays', function ($query) use ($assignment) {
                        $query->where('day_of_week', $assignment->assignment_date->dayOfWeek);
                    })
                    ->first();
                
                $assignment->collection_time = $schedule ? $schedule->collection_time : null;
                
                return $assignment;
            });
        });
        
        return view('crew.assignments.upcoming', compact('groupedAssignments'));
    }
}
