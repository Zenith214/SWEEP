<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use App\Services\ReportPhotoService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResidentReportController extends Controller
{
    protected ReportService $reportService;
    protected ReportPhotoService $reportPhotoService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ReportService $reportService,
        ReportPhotoService $reportPhotoService
    ) {
        $this->middleware(['auth', 'role:resident']);
        
        $this->reportService = $reportService;
        $this->reportPhotoService = $reportPhotoService;
    }

    /**
     * Display a listing of the resident's reports.
     */
    public function index(Request $request)
    {
        $filters = [];
        
        // Apply status filter if provided
        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }
        
        // Get resident's reports with filters
        $reports = $this->reportService->getResidentReports(
            $request->user(),
            $filters
        );
        
        return view('resident.reports.index', [
            'reports' => $reports,
            'statuses' => Report::STATUSES,
            'selectedStatus' => $request->input('status')
        ]);
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        return view('resident.reports.create', [
            'reportTypes' => Report::REPORT_TYPES
        ]);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(StoreReportRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Create the report
            $report = $this->reportService->createReport(
                $request->only(['report_type', 'location', 'description']),
                $request->user()
            );
            
            // Upload photos if provided
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $this->reportPhotoService->uploadPhoto($photo, $report);
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('resident.reports.show', $report)
                ->with('success', 'Report submitted successfully! Your reference number is: ' . $report->reference_number);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Failed to submit report: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report)
    {
        // Authorize: resident can only view their own reports
        if ($report->resident_id !== auth()->id()) {
            abort(403, 'You can only view your own reports.');
        }
        
        // Load relationships
        $report->load([
            'photos',
            'responses.admin',
            'statusHistory.changedBy',
            'route',
            'assignedTo'
        ]);
        
        return view('resident.reports.show', [
            'report' => $report
        ]);
    }

    /**
     * Search for reports by reference number.
     */
    public function search(Request $request)
    {
        $request->validate([
            'reference_number' => 'required|string'
        ]);
        
        $referenceNumber = $request->input('reference_number');
        
        // Search for the report
        $report = $this->reportService->searchByReference(
            $referenceNumber,
            $request->user()
        );
        
        if (!$report) {
            return back()->with('error', 'Report not found. Please check the reference number and try again.');
        }
        
        return redirect()->route('resident.reports.show', $report);
    }
}
