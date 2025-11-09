<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecyclingTarget;
use App\Services\RecyclingAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecyclingTargetController extends Controller
{
    protected RecyclingAnalyticsService $analyticsService;

    public function __construct(RecyclingAnalyticsService $analyticsService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display targets with progress.
     * Requirements: 12.1, 12.2, 12.4, 12.5
     */
    public function index(Request $request)
    {
        // Get month from request or default to current month
        $month = $request->input('month') 
            ? Carbon::parse($request->input('month')) 
            : Carbon::now();

        // Get target progress for the selected month
        $targetProgress = $this->analyticsService->getTargetProgress($month);

        // Get all targets ordered by month
        $allTargets = RecyclingTarget::orderBy('month', 'desc')
            ->orderBy('material_type')
            ->paginate(20);

        // Available material types
        $materialTypes = [
            'all' => 'All Materials',
            'plastic' => 'Plastic',
            'paper' => 'Paper',
            'glass' => 'Glass',
            'metal' => 'Metal',
            'cardboard' => 'Cardboard',
            'organic' => 'Organic'
        ];

        return view('admin.recycling.targets.index', [
            'month' => $month,
            'currentTargets' => $targetProgress,
            'allTargets' => $allTargets,
            'materialTypes' => $materialTypes
        ]);
    }

    /**
     * Store a new recycling target.
     * Requirements: 12.1, 12.2
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'material_type' => [
                'nullable',
                Rule::in(['plastic', 'paper', 'glass', 'metal', 'cardboard', 'organic', 'all'])
            ],
            'target_weight' => 'required|numeric|min:0.01|max:999999.99'
        ]);

        // Convert month to first day of month
        $monthDate = Carbon::parse($validated['month'] . '-01');

        // Check for duplicate target (same month + material_type)
        $exists = RecyclingTarget::where('month', $monthDate->format('Y-m-01'))
            ->where(function ($query) use ($validated) {
                if (isset($validated['material_type'])) {
                    $query->where('material_type', $validated['material_type']);
                } else {
                    $query->whereNull('material_type');
                }
            })
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A target for this month and material type already exists.');
        }

        // Create the target
        RecyclingTarget::create([
            'month' => $monthDate->format('Y-m-01'),
            'material_type' => $validated['material_type'] ?? null,
            'target_weight' => $validated['target_weight']
        ]);

        // Invalidate cache
        $this->analyticsService->invalidateCache();

        return redirect()->route('admin.recycling.targets.index')
            ->with('success', 'Recycling target created successfully.');
    }

    /**
     * Update an existing recycling target.
     * Requirements: 12.1, 12.2
     */
    public function update(Request $request, RecyclingTarget $recyclingTarget)
    {
        $validated = $request->validate([
            'target_weight' => 'required|numeric|min:0.01|max:999999.99'
        ]);

        $recyclingTarget->update([
            'target_weight' => $validated['target_weight']
        ]);

        // Invalidate cache
        $this->analyticsService->invalidateCache();

        return redirect()->route('admin.recycling.targets.index')
            ->with('success', 'Recycling target updated successfully.');
    }

    /**
     * Delete a recycling target.
     * Requirements: 12.1, 12.2
     */
    public function destroy(RecyclingTarget $recyclingTarget)
    {
        $recyclingTarget->delete();

        // Invalidate cache
        $this->analyticsService->invalidateCache();

        return redirect()->route('admin.recycling.targets.index')
            ->with('success', 'Recycling target deleted successfully.');
    }
}
