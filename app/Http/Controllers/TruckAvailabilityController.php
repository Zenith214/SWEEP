<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TruckAvailabilityController extends Controller
{
    protected AssignmentService $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display truck availability interface.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date') 
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $availability = $this->assignmentService->getTruckAvailability($selectedDate);

        return view('admin.truck-availability.index', [
            'selectedDate' => $selectedDate,
            'availability' => $availability,
        ]);
    }

    /**
     * Get truck availability data via AJAX.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->input('date'));
        $availability = $this->assignmentService->getTruckAvailability($date);

        // Format data for JSON response
        $formattedAvailability = [
            'operational' => $availability['operational']->map(function ($item) {
                return [
                    'id' => $item['truck']->id,
                    'truck_number' => $item['truck']->truck_number,
                    'license_plate' => $item['truck']->license_plate,
                    'capacity' => $item['truck']->capacity,
                    'is_available' => $item['is_available'],
                    'assignment' => $item['assignment'] ? [
                        'id' => $item['assignment']->id,
                        'route_name' => $item['assignment']->route->name,
                        'crew_name' => $item['assignment']->user->name,
                    ] : null,
                ];
            }),
            'maintenance' => $availability['maintenance']->map(function ($item) {
                return [
                    'id' => $item['truck']->id,
                    'truck_number' => $item['truck']->truck_number,
                    'license_plate' => $item['truck']->license_plate,
                    'capacity' => $item['truck']->capacity,
                    'notes' => $item['truck']->notes,
                ];
            }),
            'out_of_service' => $availability['out_of_service']->map(function ($item) {
                return [
                    'id' => $item['truck']->id,
                    'truck_number' => $item['truck']->truck_number,
                    'license_plate' => $item['truck']->license_plate,
                    'capacity' => $item['truck']->capacity,
                    'notes' => $item['truck']->notes,
                ];
            }),
        ];

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'availability' => $formattedAvailability,
        ]);
    }
}
