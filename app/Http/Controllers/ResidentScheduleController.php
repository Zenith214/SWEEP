<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ResidentScheduleController extends Controller
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
     * Display the zone search interface.
     */
    public function index(): View
    {
        return view('resident.schedules.index');
    }

    /**
     * Search for schedules by zone.
     */
    public function search(Request $request): View
    {
        $request->validate([
            'zone' => 'required|string|max:100',
        ]);

        $zone = $request->input('zone');

        // Find routes in the zone
        $routes = Route::where('zone', $zone)
            ->where('is_active', true)
            ->with(['activeSchedules.scheduleDays'])
            ->get();

        // Handle zone not found
        if ($routes->isEmpty()) {
            return view('resident.schedules.index')
                ->with('error', 'No collection schedules found for this zone. Please check your zone identifier and try again.');
        }

        // Store recent search in session
        $recentZones = session()->get('recent_zones', []);
        if (!in_array($zone, $recentZones)) {
            array_unshift($recentZones, $zone);
            $recentZones = array_slice($recentZones, 0, 5); // Keep only last 5 searches
            session()->put('recent_zones', $recentZones);
        }

        // Get next collection dates for each route
        $routesWithNextCollection = $routes->map(function ($route) {
            $nextCollection = $this->scheduleService->getNextCollectionForRoute($route);
            
            return [
                'route' => $route,
                'next_collection' => $nextCollection,
            ];
        });

        return view('resident.schedules.search', [
            'zone' => $zone,
            'routes' => $routesWithNextCollection,
        ]);
    }

    /**
     * Display the calendar view for a zone.
     */
    public function calendar(Request $request)
    {
        $request->validate([
            'zone' => 'required|string|max:100',
        ]);

        $zone = $request->input('zone');

        // Verify zone exists
        $routeExists = Route::where('zone', $zone)
            ->where('is_active', true)
            ->exists();

        if (!$routeExists) {
            return redirect()->route('resident.schedules')
                ->with('error', 'No collection schedules found for this zone.');
        }

        return view('resident.schedules.calendar', [
            'zone' => $zone,
        ]);
    }

    /**
     * Get calendar data for a zone (AJAX endpoint).
     */
    public function getCalendarData(Request $request): JsonResponse
    {
        $request->validate([
            'zone' => 'required|string|max:100',
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $zone = $request->input('zone');
        $start = Carbon::parse($request->input('start'));
        $end = Carbon::parse($request->input('end'));

        // Get collection dates for the zone
        $collectionDates = $this->scheduleService->getCollectionDatesForZone($zone, $start, $end);

        // Apply holiday exceptions
        $collectionDates = $this->scheduleService->applyHolidayExceptions($collectionDates);

        // Format for calendar
        $events = $collectionDates->map(function ($item) {
            $date = $item['date'];
            $route = $item['route'];
            $schedule = $item['schedule'];
            $collectionTime = $item['collection_time'];

            $event = [
                'title' => $route->name,
                'start' => $date->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => '#2D5F3F', // Forest Green
                'borderColor' => '#2D5F3F',
                'extendedProps' => [
                    'route_name' => $route->name,
                    'zone' => $route->zone,
                    'collection_time' => $collectionTime->format('H:i'),
                    'is_rescheduled' => false,
                ],
            ];

            // Check if this is a rescheduled date
            if (isset($item['is_rescheduled']) && $item['is_rescheduled']) {
                $event['backgroundColor'] = '#F59E0B'; // Amber
                $event['borderColor'] = '#F59E0B';
                $event['extendedProps']['is_rescheduled'] = true;
                $event['extendedProps']['original_date'] = $item['original_date']->format('Y-m-d');
                $event['extendedProps']['holiday_name'] = $item['holiday']->name ?? '';
            }

            return $event;
        });

        return response()->json($events->values());
    }
}
