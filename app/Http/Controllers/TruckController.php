<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTruckRequest;
use App\Http\Requests\UpdateTruckRequest;
use App\Http\Requests\UpdateTruckStatusRequest;
use App\Models\Truck;
use App\Models\TruckStatusHistory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TruckController extends Controller
{
    /**
     * Ensure only administrators can access truck management.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:administrator']);
    }

    /**
     * Display a listing of trucks with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Truck::withCount('activeAssignments');

        // Search functionality by truck number or license plate
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('truck_number', 'like', "%{$search}%")
                  ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        // Filter by operational status
        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('operational_status', $status);
        }

        // Pagination
        $trucks = $query->orderBy('truck_number')->paginate(15)->withQueryString();

        return view('admin.trucks.index', compact('trucks'));
    }

    /**
     * Show the form for creating a new truck.
     */
    public function create(): View
    {
        return view('admin.trucks.create');
    }

    /**
     * Store a newly created truck in storage.
     */
    public function store(StoreTruckRequest $request): RedirectResponse
    {
        try {
            $truck = Truck::create($request->validated());

            return redirect()->route('admin.trucks.index')
                ->with('success', 'Truck registered successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to register truck: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified truck with assignment history.
     */
    public function show(Truck $truck): View
    {
        // Load relationships
        $truck->load(['statusHistory' => function ($query) {
            $query->with('changedBy')->orderBy('created_at', 'desc');
        }]);

        // Get assignment history for the last 90 days by default
        $startDate = Carbon::now()->subDays(90);
        $endDate = Carbon::now();
        
        $assignmentHistory = $truck->getAssignmentHistory($startDate, $endDate);
        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);

        return view('admin.trucks.show', compact('truck', 'assignmentHistory', 'utilizationRate', 'startDate', 'endDate'));
    }

    /**
     * Show the form for editing the specified truck.
     */
    public function edit(Truck $truck): View
    {
        return view('admin.trucks.edit', compact('truck'));
    }

    /**
     * Update the specified truck in storage.
     */
    public function update(UpdateTruckRequest $request, Truck $truck): RedirectResponse
    {
        try {
            $truck->update($request->validated());

            return redirect()->route('admin.trucks.show', $truck)
                ->with('success', 'Truck updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update truck: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified truck from storage (soft delete).
     */
    public function destroy(Truck $truck): RedirectResponse
    {
        // Check for future assignments
        if ($truck->hasFutureAssignments()) {
            return redirect()->back()
                ->with('error', 'Cannot delete truck with future assignments. Please cancel or reassign them first.');
        }

        try {
            $truck->delete();

            return redirect()->route('admin.trucks.index')
                ->with('success', 'Truck deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete truck: ' . $e->getMessage());
        }
    }

    /**
     * Update the operational status of a truck.
     */
    public function updateStatus(UpdateTruckStatusRequest $request, Truck $truck): RedirectResponse
    {
        $validated = $request->validated();
        $oldStatus = $truck->operational_status;
        $newStatus = $validated['operational_status'];

        // Check if status is actually changing
        if ($oldStatus === $newStatus) {
            return redirect()->back()
                ->with('info', 'Truck status is already set to ' . $newStatus . '.');
        }

        // Check for future assignments and provide warning
        $hasFutureAssignments = $truck->hasFutureAssignments();
        
        try {
            // Update truck status
            $truck->operational_status = $newStatus;
            $truck->save();

            // Log status change to history
            TruckStatusHistory::create([
                'truck_id' => $truck->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $message = 'Truck status updated successfully.';
            
            if ($hasFutureAssignments && ($newStatus === Truck::STATUS_MAINTENANCE || $newStatus === Truck::STATUS_OUT_OF_SERVICE)) {
                $message .= ' Warning: This truck has future assignments that may be affected.';
            }

            return redirect()->route('admin.trucks.show', $truck)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update truck status: ' . $e->getMessage());
        }
    }

    /**
     * Display assignment history for a truck with date range filter.
     */
    public function history(Request $request, Truck $truck): View
    {
        // Get date range from request or use defaults
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->input('start_date'))
            : Carbon::now()->subDays(90);
        
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))
            : Carbon::now();

        // Get assignment history
        $assignmentHistory = $truck->getAssignmentHistory($startDate, $endDate);
        
        // Calculate utilization rate
        $utilizationRate = $truck->getUtilizationRate($startDate, $endDate);
        
        // Get total assignment count
        $totalAssignments = $assignmentHistory->count();

        return view('admin.trucks.history', compact('truck', 'assignmentHistory', 'utilizationRate', 'totalAssignments', 'startDate', 'endDate'));
    }
}
