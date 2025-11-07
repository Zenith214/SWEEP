<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Requests\UpdateRouteRequest;
use App\Models\Route;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteController extends Controller
{
    /**
     * Display a listing of routes with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Route::withCount('activeSchedules');

        // Search functionality by name or zone
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('zone', 'like', "%{$search}%");
            });
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
        $routes = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.routes.index', compact('routes'));
    }

    /**
     * Show the form for creating a new route.
     */
    public function create(): View
    {
        return view('admin.routes.create');
    }

    /**
     * Store a newly created route in storage.
     */
    public function store(StoreRouteRequest $request): RedirectResponse
    {
        $route = Route::create($request->validated());

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route created successfully.');
    }

    /**
     * Display the specified route with schedule details.
     */
    public function show(Route $route): View
    {
        $route->load(['schedules' => function ($query) {
            $query->with('scheduleDays')->orderBy('start_date');
        }]);

        return view('admin.routes.show', compact('route'));
    }

    /**
     * Show the form for editing the specified route.
     */
    public function edit(Route $route): View
    {
        return view('admin.routes.edit', compact('route'));
    }

    /**
     * Update the specified route in storage.
     */
    public function update(UpdateRouteRequest $request, Route $route): RedirectResponse
    {
        $route->update($request->validated());

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route updated successfully.');
    }

    /**
     * Remove the specified route from storage (soft delete).
     */
    public function destroy(Route $route): RedirectResponse
    {
        // Check for active schedules before deletion
        if ($route->hasActiveSchedules()) {
            return back()->with('error', 'Cannot delete route with active schedules. Please deactivate or delete schedules first.');
        }

        $route->delete();

        return redirect()->route('admin.routes.index')
            ->with('success', 'Route deleted successfully.');
    }
}
