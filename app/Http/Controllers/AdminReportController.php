<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddResponseRequest;
use App\Http\Requests\AssignReportRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Report;
use App\Models\Route;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->middleware(['auth', 'role:administrator']);
        $this->reportService = $reportService;
    }

    /**
     * Display all reports with filters (status, type, date range).
     */
    public function index(Request $request)
    {
        $filters = [];

        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        if ($request->filled('report_type')) {
            $filters['report_type'] = $request->report_type;
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        $reports = $this->reportService->getReportsWithFilters($filters);

        return view('admin.reports.index', [
            'reports' => $reports,
            'reportTypes' => Report::REPORT_TYPES,
            'statuses' => Report::STATUSES,
            'filters' => $filters
        ]);
    }

    /**
     * Display detailed report view.
     */
    public function show(Report $report)
    {
        $report->load([
            'resident',
            'photos',
            'responses.admin',
            'statusHistory.changedBy',
            'route',
            'assignedTo'
        ]);

        // Get available routes and collection crew members for assignment
        $routes = Route::orderBy('name')->get();
        $crewMembers = User::role('collection_crew')->orderBy('name')->get();

        return view('admin.reports.show', [
            'report' => $report,
            'reportTypes' => Report::REPORT_TYPES,
            'statuses' => Report::STATUSES,
            'routes' => $routes,
            'crewMembers' => $crewMembers
        ]);
    }

    /**
     * Update report status with note.
     */
    public function updateStatus(UpdateStatusRequest $request, Report $report)
    {
        $this->reportService->updateStatus(
            $report,
            $request->status,
            $request->user(),
            $request->note
        );

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Report status updated successfully.');
    }

    /**
     * Add administrator response to report.
     */
    public function addResponse(AddResponseRequest $request, Report $report)
    {
        $this->reportService->addResponse(
            $report,
            $request->response,
            $request->user()
        );

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Response added successfully.');
    }

    /**
     * Assign report to route or crew member.
     */
    public function assign(AssignReportRequest $request, Report $report)
    {
        $this->reportService->assignReport(
            $report,
            $request->route_id,
            $request->assigned_to
        );

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Report assigned successfully.');
    }

    /**
     * Remove assignment from report.
     */
    public function unassign(Report $report)
    {
        $this->reportService->assignReport($report, null, null);

        return redirect()
            ->route('admin.reports.show', $report)
            ->with('success', 'Assignment removed successfully.');
    }
}
