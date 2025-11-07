<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Schedule;
use App\Models\ScheduleDay;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Create a new schedule with days.
     *
     * @param array $data
     * @return Schedule
     * @throws \Exception
     */
    public function createSchedule(array $data): Schedule
    {
        return DB::transaction(function () use ($data) {
            // Extract days from data
            $daysOfWeek = $data['days_of_week'] ?? [];
            unset($data['days_of_week']);

            // Create the schedule
            $schedule = Schedule::create($data);

            // Create schedule days
            foreach ($daysOfWeek as $day) {
                ScheduleDay::create([
                    'schedule_id' => $schedule->id,
                    'day_of_week' => $day,
                ]);
            }

            // Reload the schedule with relationships
            $schedule->load('scheduleDays');

            return $schedule;
        });
    }

    /**
     * Update a schedule with conflict checking.
     *
     * @param Schedule $schedule
     * @param array $data
     * @return Schedule
     * @throws \Exception
     */
    public function updateSchedule(Schedule $schedule, array $data): Schedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            // Extract days from data
            $daysOfWeek = $data['days_of_week'] ?? null;
            unset($data['days_of_week']);

            // Update the schedule
            $schedule->update($data);

            // Update schedule days if provided
            if ($daysOfWeek !== null) {
                // Delete existing days
                $schedule->scheduleDays()->delete();

                // Create new days
                foreach ($daysOfWeek as $day) {
                    ScheduleDay::create([
                        'schedule_id' => $schedule->id,
                        'day_of_week' => $day,
                    ]);
                }
            }

            // Reload the schedule with relationships
            $schedule->load('scheduleDays');

            // Check for conflicts
            if ($this->checkConflicts($schedule, $schedule)) {
                throw new \Exception('This schedule conflicts with an existing schedule on the same route');
            }

            return $schedule;
        });
    }

    /**
     * Duplicate a schedule to another route.
     *
     * @param Schedule $schedule
     * @param Route $targetRoute
     * @return Schedule
     * @throws \Exception
     */
    public function duplicateSchedule(Schedule $schedule, Route $targetRoute): Schedule
    {
        return DB::transaction(function () use ($schedule, $targetRoute) {
            // Create new schedule with same attributes
            $newSchedule = Schedule::create([
                'route_id' => $targetRoute->id,
                'collection_time' => $schedule->collection_time,
                'start_date' => $schedule->start_date,
                'end_date' => $schedule->end_date,
                'is_active' => $schedule->is_active,
            ]);

            // Copy schedule days
            foreach ($schedule->scheduleDays as $scheduleDay) {
                ScheduleDay::create([
                    'schedule_id' => $newSchedule->id,
                    'day_of_week' => $scheduleDay->day_of_week,
                ]);
            }

            // Reload the schedule with relationships
            $newSchedule->load('scheduleDays');

            // Check for conflicts (exclude the newly created schedule itself)
            if ($this->checkConflicts($newSchedule, $newSchedule)) {
                throw new \Exception('Cannot duplicate schedule - would create conflict on target route');
            }

            return $newSchedule;
        });
    }

    /**
     * Check if a schedule has conflicts with other schedules on the same route.
     *
     * @param Schedule $schedule
     * @param Schedule|null $exclude Schedule to exclude from conflict check (for updates)
     * @return bool
     */
    public function checkConflicts(Schedule $schedule, ?Schedule $exclude = null): bool
    {
        $query = Schedule::where('route_id', $schedule->route_id)
            ->where('is_active', true);

        // Exclude the schedule being updated
        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        $otherSchedules = $query->with('scheduleDays')->get();

        foreach ($otherSchedules as $otherSchedule) {
            if ($schedule->hasConflictWith($otherSchedule)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all collection dates for a zone in a date range.
     *
     * @param string $zone
     * @param Carbon $start
     * @param Carbon $end
     * @return Collection
     */
    public function getCollectionDatesForZone(string $zone, Carbon $start, Carbon $end): Collection
    {
        // Get all routes in the zone
        $routes = Route::where('zone', $zone)
            ->where('is_active', true)
            ->with(['activeSchedules.scheduleDays'])
            ->get();

        $allDates = collect();

        foreach ($routes as $route) {
            foreach ($route->activeSchedules as $schedule) {
                $dates = $schedule->getCollectionDatesInRange($start, $end);
                
                // Add route and schedule information to each date
                foreach ($dates as $date) {
                    $allDates->push([
                        'date' => $date,
                        'route' => $route,
                        'schedule' => $schedule,
                        'collection_time' => $schedule->collection_time,
                    ]);
                }
            }
        }

        // Sort by date
        return $allDates->sortBy('date')->values();
    }

    /**
     * Get the next collection date and time for a route.
     *
     * @param Route $route
     * @return array|null
     */
    public function getNextCollectionForRoute(Route $route): ?array
    {
        $nextDate = $route->getNextCollectionDate();

        if (!$nextDate) {
            return null;
        }

        // Find the schedule that matches this date
        foreach ($route->activeSchedules as $schedule) {
            if ($schedule->isActiveOn($nextDate)) {
                return [
                    'date' => $nextDate,
                    'time' => $schedule->collection_time,
                    'schedule' => $schedule,
                ];
            }
        }

        return null;
    }

    /**
     * Apply holiday exceptions to a collection of dates.
     * Filters out holidays and applies rescheduled dates.
     *
     * @param Collection $dates
     * @return Collection
     */
    public function applyHolidayExceptions(Collection $dates): Collection
    {
        if ($dates->isEmpty()) {
            return $dates;
        }

        // Get the date range
        $firstDate = $dates->min(function ($item) {
            return is_array($item) ? $item['date'] : $item;
        });
        $lastDate = $dates->max(function ($item) {
            return is_array($item) ? $item['date'] : $item;
        });

        // Get holidays in range
        $holidays = Holiday::getHolidaysInRange($firstDate, $lastDate);

        // Create a map of holidays
        $holidayMap = [];
        foreach ($holidays as $holiday) {
            $holidayMap[$holiday->date->format('Y-m-d')] = $holiday;
        }

        $processedDates = collect();

        foreach ($dates as $item) {
            $date = is_array($item) ? $item['date'] : $item;
            $dateKey = $date->format('Y-m-d');

            // Check if this date is a holiday
            if (isset($holidayMap[$dateKey])) {
                $holiday = $holidayMap[$dateKey];

                // If collection is skipped, don't include this date
                if ($holiday->is_collection_skipped) {
                    continue;
                }

                // If rescheduled, update the date
                if ($holiday->reschedule_date) {
                    if (is_array($item)) {
                        $item['date'] = $holiday->reschedule_date;
                        $item['is_rescheduled'] = true;
                        $item['original_date'] = $date;
                        $item['holiday'] = $holiday;
                    } else {
                        $item = $holiday->reschedule_date;
                    }
                }
            }

            $processedDates->push($item);
        }

        return $processedDates;
    }

    /**
     * Get routes that have no active schedules.
     *
     * @return Collection
     */
    public function getRoutesWithoutSchedules(): Collection
    {
        return Route::where('is_active', true)
            ->whereDoesntHave('activeSchedules')
            ->get();
    }

    /**
     * Get schedule coverage statistics.
     *
     * @return array
     */
    public function getScheduleCoverage(): array
    {
        $totalRoutes = Route::where('is_active', true)->count();
        $routesWithSchedules = Route::where('is_active', true)
            ->whereHas('activeSchedules')
            ->count();

        $percentage = $totalRoutes > 0 
            ? round(($routesWithSchedules / $totalRoutes) * 100, 2)
            : 0;

        return [
            'total_routes' => $totalRoutes,
            'routes_with_schedules' => $routesWithSchedules,
            'routes_without_schedules' => $totalRoutes - $routesWithSchedules,
            'coverage_percentage' => $percentage,
        ];
    }
}
