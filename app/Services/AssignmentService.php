<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Truck;
use App\Models\User;
use App\Models\Route;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssignmentService
{
    /**
     * Create a new assignment with validation.
     *
     * @param array $data
     * @return Assignment
     * @throws \Exception
     */
    public function createAssignment(array $data): Assignment
    {
        // Validate truck is operational
        $truck = Truck::findOrFail($data['truck_id']);
        if (!$truck->isOperational()) {
            throw new \Exception("This truck is not operational. Current status: {$truck->operational_status}");
        }

        // Validate user has collection_crew role
        $user = User::findOrFail($data['user_id']);
        if (!$user->hasRole('collection_crew')) {
            throw new \Exception("Selected user is not a collection crew member");
        }

        // Check for conflicts
        $conflicts = $this->checkConflicts($data);
        if (!empty($conflicts)) {
            throw new \Exception(implode('. ', $conflicts));
        }

        // Create the assignment
        return Assignment::create([
            'truck_id' => $data['truck_id'],
            'user_id' => $data['user_id'],
            'route_id' => $data['route_id'],
            'assignment_date' => $data['assignment_date'],
            'status' => Assignment::STATUS_ACTIVE,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update an assignment with conflict checking.
     *
     * @param Assignment $assignment
     * @param array $data
     * @return Assignment
     * @throws \Exception
     */
    public function updateAssignment(Assignment $assignment, array $data): Assignment
    {
        // Validate truck is operational
        $truck = Truck::findOrFail($data['truck_id']);
        if (!$truck->isOperational()) {
            throw new \Exception("This truck is not operational. Current status: {$truck->operational_status}");
        }

        // Validate user has collection_crew role
        $user = User::findOrFail($data['user_id']);
        if (!$user->hasRole('collection_crew')) {
            throw new \Exception("Selected user is not a collection crew member");
        }

        // Check for conflicts (excluding current assignment)
        $conflicts = $this->checkConflicts($data, $assignment);
        if (!empty($conflicts)) {
            throw new \Exception(implode('. ', $conflicts));
        }

        // Update the assignment
        $assignment->update([
            'truck_id' => $data['truck_id'],
            'user_id' => $data['user_id'],
            'route_id' => $data['route_id'],
            'assignment_date' => $data['assignment_date'],
            'notes' => $data['notes'] ?? $assignment->notes,
        ]);

        return $assignment->fresh();
    }

    /**
     * Cancel an assignment.
     *
     * @param Assignment $assignment
     * @param string|null $reason
     * @return void
     */
    public function cancelAssignment(Assignment $assignment, ?string $reason = null): void
    {
        $assignment->status = Assignment::STATUS_CANCELLED;
        
        if ($reason) {
            $assignment->cancellation_reason = $reason;
        }
        
        $assignment->save();
    }

    /**
     * Check for conflicts with truck and crew assignments.
     *
     * @param array $data
     * @param Assignment|null $exclude
     * @return array
     */
    public function checkConflicts(array $data, ?Assignment $exclude = null): array
    {
        $conflicts = [];
        $date = Carbon::parse($data['assignment_date']);

        // Check for truck conflict
        $truckConflict = Assignment::active()
            ->where('truck_id', $data['truck_id'])
            ->forDate($date)
            ->when($exclude, function ($query) use ($exclude) {
                return $query->where('id', '!=', $exclude->id);
            })
            ->exists();

        if ($truckConflict) {
            $conflicts[] = "This truck is already assigned to another route on this date";
        }

        // Check for crew conflict
        $crewConflict = Assignment::active()
            ->where('user_id', $data['user_id'])
            ->forDate($date)
            ->when($exclude, function ($query) use ($exclude) {
                return $query->where('id', '!=', $exclude->id);
            })
            ->exists();

        if ($crewConflict) {
            $conflicts[] = "This crew member is already assigned to another route on this date";
        }

        return $conflicts;
    }

    /**
     * Copy assignments from one date to another with conflict detection.
     *
     * @param Carbon $sourceDate
     * @param Carbon $targetDate
     * @param array|null $filters
     * @return array
     */
    public function copyAssignments(Carbon $sourceDate, Carbon $targetDate, ?array $filters = null): array
    {
        $sourceAssignments = Assignment::active()
            ->forDate($sourceDate)
            ->with(['truck', 'user', 'route'])
            ->when($filters && isset($filters['truck_ids']), function ($query) use ($filters) {
                return $query->whereIn('truck_id', $filters['truck_ids']);
            })
            ->get();

        $results = [
            'success' => [],
            'conflicts' => [],
        ];

        foreach ($sourceAssignments as $sourceAssignment) {
            $data = [
                'truck_id' => $sourceAssignment->truck_id,
                'user_id' => $sourceAssignment->user_id,
                'route_id' => $sourceAssignment->route_id,
                'assignment_date' => $targetDate->format('Y-m-d'),
                'notes' => $sourceAssignment->notes,
            ];

            try {
                $newAssignment = $this->createAssignment($data);
                $results['success'][] = [
                    'assignment' => $newAssignment,
                    'truck' => $sourceAssignment->truck->truck_number,
                    'route' => $sourceAssignment->route->name,
                ];
            } catch (\Exception $e) {
                $results['conflicts'][] = [
                    'truck_number' => $sourceAssignment->truck->truck_number,
                    'route_name' => $sourceAssignment->route->name,
                    'crew_name' => $sourceAssignment->user->name,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get all active assignments for a specific date.
     *
     * @param Carbon $date
     * @return Collection
     */
    public function getAssignmentsForDate(Carbon $date): Collection
    {
        return Assignment::active()
            ->forDate($date)
            ->with(['truck', 'user', 'route'])
            ->orderBy('truck_id')
            ->get();
    }

    /**
     * Get assignments in a date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getAssignmentsInRange(Carbon $start, Carbon $end): Collection
    {
        return Assignment::active()
            ->whereBetween('assignment_date', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d')
            ])
            ->with(['truck', 'user', 'route'])
            ->orderBy('assignment_date')
            ->orderBy('truck_id')
            ->get();
    }

    /**
     * Get routes with schedules but no assignments in the date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getUnassignedRoutes(Carbon $start, Carbon $end): Collection
    {
        $unassignedRoutes = collect();
        
        // Get all active routes with schedules
        $routes = Route::where('is_active', true)
            ->with(['activeSchedules.scheduleDays'])
            ->get();

        foreach ($routes as $route) {
            foreach ($route->activeSchedules as $schedule) {
                // Get collection dates for this schedule in the range
                $collectionDates = $schedule->getCollectionDatesInRange($start, $end);
                
                foreach ($collectionDates as $date) {
                    // Check if there's an assignment for this route on this date
                    $hasAssignment = Assignment::active()
                        ->where('route_id', $route->id)
                        ->forDate($date)
                        ->exists();
                    
                    if (!$hasAssignment) {
                        $unassignedRoutes->push([
                            'route' => $route,
                            'schedule' => $schedule,
                            'date' => $date,
                            'collection_time' => $schedule->collection_time,
                        ]);
                    }
                }
            }
        }

        return $unassignedRoutes->sortBy('date');
    }

    /**
     * Get truck availability for a specific date.
     *
     * @param Carbon $date
     * @return array
     */
    public function getTruckAvailability(Carbon $date): array
    {
        $trucks = Truck::with(['activeAssignments' => function ($query) use ($date) {
            $query->forDate($date)->with(['route', 'user']);
        }])->get();

        $availability = [
            'operational' => [],
            'maintenance' => [],
            'out_of_service' => [],
        ];

        foreach ($trucks as $truck) {
            $assignment = $truck->activeAssignments->first();
            
            $truckData = [
                'truck' => $truck,
                'assignment' => $assignment,
                'is_available' => $assignment === null,
            ];

            if ($truck->operational_status === Truck::STATUS_OPERATIONAL) {
                $availability['operational'][] = $truckData;
            } elseif ($truck->operational_status === Truck::STATUS_MAINTENANCE) {
                $availability['maintenance'][] = $truckData;
            } else {
                $availability['out_of_service'][] = $truckData;
            }
        }

        return $availability;
    }

    /**
     * Get assignments for a specific crew member in a date range.
     *
     * @param User $user
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getCrewAssignments(User $user, Carbon $start, Carbon $end): Collection
    {
        return Assignment::active()
            ->where('user_id', $user->id)
            ->whereDate('assignment_date', '>=', $start)
            ->whereDate('assignment_date', '<=', $end)
            ->with(['truck', 'route.activeSchedules'])
            ->orderBy('assignment_date')
            ->get();
    }
}
