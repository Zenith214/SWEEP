<?php

namespace App\Http\Controllers;

use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }
    /**
     * Route to role-specific dashboard based on user role.
     * Requirements: 4.1, 4.2, 4.3
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('collection_crew')) {
            return redirect()->route('crew.dashboard');
        }

        if ($user->hasRole('resident')) {
            return redirect()->route('resident.dashboard');
        }

        // Fallback if no role is assigned
        abort(403, 'No role assigned to your account. Please contact an administrator.');
    }

    /**
     * Display the administrator dashboard.
     * Requirements: 4.1, 8.1, 12.1, 12.2, 12.3, 12.4, 12.5
     */
    public function adminDashboard(Request $request): View
    {
        $user = $request->user();
        
        // Get all assignment alerts
        $allAlerts = $this->alertService->getAssignmentAlerts();
        
        // Filter out dismissed alerts
        $alerts = array_filter($allAlerts, function ($alert) use ($user) {
            return !$this->alertService->isAlertDismissed($alert['type'], $user);
        });
        
        return view('dashboards.admin', [
            'user' => $user,
            'alerts' => array_values($alerts), // Re-index array after filtering
        ]);
    }

    /**
     * Display the collection crew dashboard.
     * Requirements: 4.2, 8.2
     */
    public function crewDashboard(Request $request): View
    {
        return view('dashboards.crew', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the resident dashboard.
     * Requirements: 4.3, 8.3
     */
    public function residentDashboard(Request $request): View
    {
        return view('dashboards.resident', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Dismiss an alert for the current user.
     * Requirements: 12.4, 12.5
     */
    public function dismissAlert(Request $request): RedirectResponse
    {
        $request->validate([
            'alert_type' => 'required|string|in:unassigned_routes,underutilized_trucks',
        ]);

        $this->alertService->dismissAlert($request->input('alert_type'), $request->user());

        return redirect()->back()->with('success', 'Alert dismissed');
    }
}
