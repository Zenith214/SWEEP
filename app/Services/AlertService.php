<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Truck;
use App\Models\Route;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AlertService
{
    /**
     * Get all assignment-related alerts for the dashboard.
     *
     * @return array
     */
    public function getAssignmentAlerts(): array
    {
        $alerts = [];

        $unassignedRoutesAlert = $this->getUnassignedRoutesAlert();
        if ($unassignedRoutesAlert) {
            $alerts[] = $unassignedRoutesAlert;
        }

        $underutilizedTrucksAlert = $this->getUnderutilizedTrucksAlert();
        if ($underutilizedTrucksAlert) {
            $alerts[] = $underutilizedTrucksAlert;
        }

        return $alerts;
    }

    /**
     * Get alert for routes without assignments in the next 3 days.
     *
     * @return array|null
     */
    public function getUnassignedRoutesAlert(): ?array
    {
        $start = now()->startOfDay();
        $end = now()->addDays(3)->endOfDay();

        $unassignedCount = 0;
        
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
                        $unassignedCount++;
                    }
                }
            }
        }

        if ($unassignedCount === 0) {
            return null;
        }

        return [
            'type' => 'unassigned_routes',
            'title' => 'Unassigned Routes',
            'message' => "There " . ($unassignedCount === 1 ? 'is' : 'are') . " {$unassignedCount} route" . ($unassignedCount === 1 ? '' : 's') . " without assignments in the next 3 days",
            'count' => $unassignedCount,
            'severity' => 'warning',
            'link' => $this->getRouteUrl('admin.assignments.unassigned-routes'),
            'link_text' => 'View Unassigned Routes',
        ];
    }

    /**
     * Get alert for operational trucks with no assignments in the next 7 days.
     *
     * @return array|null
     */
    public function getUnderutilizedTrucksAlert(): ?array
    {
        $start = now()->startOfDay();
        $end = now()->addDays(7)->endOfDay();

        // Get all operational trucks
        $operationalTrucks = Truck::where('operational_status', Truck::STATUS_OPERATIONAL)->get();

        $underutilizedCount = 0;

        foreach ($operationalTrucks as $truck) {
            // Check if truck has any assignments in the next 7 days
            $hasAssignments = Assignment::active()
                ->where('truck_id', $truck->id)
                ->whereBetween('assignment_date', [
                    $start->format('Y-m-d'),
                    $end->format('Y-m-d')
                ])
                ->exists();

            if (!$hasAssignments) {
                $underutilizedCount++;
            }
        }

        if ($underutilizedCount === 0) {
            return null;
        }

        return [
            'type' => 'underutilized_trucks',
            'title' => 'Underutilized Trucks',
            'message' => "There " . ($underutilizedCount === 1 ? 'is' : 'are') . " {$underutilizedCount} operational truck" . ($underutilizedCount === 1 ? '' : 's') . " with no assignments in the next 7 days",
            'count' => $underutilizedCount,
            'severity' => 'info',
            'link' => $this->getRouteUrl('admin.truck-availability.index'),
            'link_text' => 'View Truck Availability',
        ];
    }

    /**
     * Mark an alert as dismissed for a specific user.
     *
     * @param string $alertType
     * @param User $user
     * @return void
     */
    public function dismissAlert(string $alertType, User $user): void
    {
        $cacheKey = "alert_dismissed_{$alertType}_{$user->id}";
        
        // Store dismissal for 24 hours
        Cache::put($cacheKey, true, now()->addHours(24));
    }

    /**
     * Check if an alert has been dismissed by a user.
     *
     * @param string $alertType
     * @param User $user
     * @return bool
     */
    public function isAlertDismissed(string $alertType, User $user): bool
    {
        $cacheKey = "alert_dismissed_{$alertType}_{$user->id}";
        
        return Cache::has($cacheKey);
    }

    /**
     * Get route URL if it exists, otherwise return null.
     *
     * @param string $routeName
     * @return string|null
     */
    protected function getRouteUrl(string $routeName): ?string
    {
        try {
            return route($routeName);
        } catch (\Exception $e) {
            return null;
        }
    }
}
