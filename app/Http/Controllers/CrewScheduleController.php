<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrewScheduleController extends Controller
{
    protected ScheduleService $scheduleService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display today's assigned routes for crew members.
     * Shows current day's scheduled routes with details.
     */
    public function index()
    {
        $today = now()->startOfDay();
        $todayDayOfWeek = $today->dayOfWeek;

        // Get all active routes with schedules for today
        $routes = Route::where('is_active', true)
            ->with(['activeSchedules' => function ($query) use ($today, $todayDayOfWeek) {
                $query->whereHas('scheduleDays', function ($q) use ($todayDayOfWeek) {
                    $q->where('day_of_week', $todayDayOfWeek);
                })
                ->where('start_date', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $today);
                });
            }, 'activeSchedules.scheduleDays'])
            ->get()
            ->filter(function ($route) {
                return $route->activeSchedules->isNotEmpty();
            })
            ->sortBy(function ($route) {
                // Sort by collection time
                $firstSchedule = $route->activeSchedules->first();
                return $firstSchedule ? $firstSchedule->collection_time->format('H:i') : '23:59';
            });

        return view('crew.schedules.index', compact('routes', 'today'));
    }

    /**
     * Display upcoming routes for the next 7 days.
     * Groups routes by date for easy viewing.
     */
    public function upcoming()
    {
        $startDate = now()->startOfDay();
        $endDate = now()->addDays(7)->endOfDay();

        // Get all active routes with their schedules
        $routes = Route::where('is_active', true)
            ->with(['activeSchedules.scheduleDays'])
            ->get();

        // Group routes by date
        $routesByDate = collect();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            $dateKey = $date->format('Y-m-d');

            $routesForDate = $routes->filter(function ($route) use ($date, $dayOfWeek) {
                return $route->activeSchedules->filter(function ($schedule) use ($date, $dayOfWeek) {
                    return $schedule->isActiveOn($date);
                })->isNotEmpty();
            })->map(function ($route) use ($date, $dayOfWeek) {
                // Get the schedule for this date
                $schedule = $route->activeSchedules->first(function ($schedule) use ($date) {
                    return $schedule->isActiveOn($date);
                });

                return [
                    'route' => $route,
                    'schedule' => $schedule,
                    'collection_time' => $schedule ? $schedule->collection_time : null,
                ];
            })->sortBy(function ($item) {
                return $item['collection_time'] ? $item['collection_time']->format('H:i') : '23:59';
            })->values();

            if ($routesForDate->isNotEmpty()) {
                $routesByDate->push([
                    'date' => $date->copy(),
                    'routes' => $routesForDate,
                ]);
            }
        }

        return view('crew.schedules.upcoming', compact('routesByDate'));
    }

    /**
     * Display detailed information for a specific route.
     * Shows zone, schedule details, and special instructions.
     */
    public function show(Route $route)
    {
        // Load active schedules with days
        $route->load(['activeSchedules.scheduleDays']);

        // Get next collection information
        $nextCollection = $this->scheduleService->getNextCollectionForRoute($route);

        return view('crew.schedules.show', compact('route', 'nextCollection'));
    }
}
