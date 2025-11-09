<?php

namespace App\Http\Controllers;

use App\Models\ScheduledReport;
use App\Models\GeneratedReport;
use App\Jobs\GenerateScheduledReportJob;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ScheduledReportController extends Controller
{
    /**
     * Display a listing of scheduled reports.
     * Requirements: 17.1, 17.5
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get scheduled reports for the current user
        $scheduledReports = ScheduledReport::where('user_id', $user->id)
            ->with(['generatedReports' => function ($query) {
                $query->latest()->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get available metrics for selection
        $availableMetrics = ScheduledReport::getAvailableMetrics();
        
        return view('admin.scheduled-reports.index', [
            'scheduledReports' => $scheduledReports,
            'availableMetrics' => $availableMetrics,
        ]);
    }

    /**
     * Show the form for creating a new scheduled report.
     * Requirements: 17.1, 17.2, 17.3
     */
    public function create(): View
    {
        $availableMetrics = ScheduledReport::getAvailableMetrics();
        $frequencies = ScheduledReport::FREQUENCIES;
        $formats = ScheduledReport::FORMATS;
        
        return view('admin.scheduled-reports.create', [
            'availableMetrics' => $availableMetrics,
            'frequencies' => $frequencies,
            'formats' => $formats,
        ]);
    }

    /**
     * Store a newly created scheduled report.
     * Requirements: 17.1, 17.2, 17.3
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'string',
            'format' => 'required|in:pdf,csv',
            'is_active' => 'boolean',
        ]);
        
        $user = $request->user();
        
        // Create the scheduled report
        $scheduledReport = ScheduledReport::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'frequency' => $validated['frequency'],
            'metrics' => $validated['metrics'],
            'format' => $validated['format'],
            'is_active' => $validated['is_active'] ?? true,
            'next_generation_at' => now()->addDay()->startOfDay(),
        ]);
        
        return redirect()
            ->route('admin.scheduled-reports.index')
            ->with('success', 'Scheduled report created successfully.');
    }

    /**
     * Display the specified scheduled report.
     * Requirements: 17.4
     */
    public function show(ScheduledReport $scheduledReport): View
    {
        // Ensure user can only view their own reports
        $this->authorize('view', $scheduledReport);
        
        $scheduledReport->load(['generatedReports' => function ($query) {
            $query->latest()->paginate(20);
        }]);
        
        return view('admin.scheduled-reports.show', [
            'scheduledReport' => $scheduledReport,
        ]);
    }

    /**
     * Show the form for editing the specified scheduled report.
     * Requirements: 17.1, 17.2, 17.3
     */
    public function edit(ScheduledReport $scheduledReport): View
    {
        // Ensure user can only edit their own reports
        $this->authorize('update', $scheduledReport);
        
        $availableMetrics = ScheduledReport::getAvailableMetrics();
        $frequencies = ScheduledReport::FREQUENCIES;
        $formats = ScheduledReport::FORMATS;
        
        return view('admin.scheduled-reports.edit', [
            'scheduledReport' => $scheduledReport,
            'availableMetrics' => $availableMetrics,
            'frequencies' => $frequencies,
            'formats' => $formats,
        ]);
    }

    /**
     * Update the specified scheduled report.
     * Requirements: 17.1, 17.2, 17.3
     */
    public function update(Request $request, ScheduledReport $scheduledReport): RedirectResponse
    {
        // Ensure user can only update their own reports
        $this->authorize('update', $scheduledReport);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'string',
            'format' => 'required|in:pdf,csv',
        ]);
        
        $scheduledReport->update($validated);
        
        // Recalculate next generation date if frequency changed
        if ($scheduledReport->wasChanged('frequency')) {
            $scheduledReport->updateNextGenerationDate();
        }
        
        return redirect()
            ->route('admin.scheduled-reports.index')
            ->with('success', 'Scheduled report updated successfully.');
    }

    /**
     * Remove the specified scheduled report.
     * Requirements: 17.5
     */
    public function destroy(ScheduledReport $scheduledReport): RedirectResponse
    {
        // Ensure user can only delete their own reports
        $this->authorize('delete', $scheduledReport);
        
        $scheduledReport->delete();
        
        return redirect()
            ->route('admin.scheduled-reports.index')
            ->with('success', 'Scheduled report deleted successfully.');
    }

    /**
     * Toggle the active status of a scheduled report.
     * Requirements: 17.5
     */
    public function toggleActive(Request $request, ScheduledReport $scheduledReport): RedirectResponse
    {
        // Ensure user can only toggle their own reports
        $this->authorize('update', $scheduledReport);
        
        if ($scheduledReport->is_active) {
            $scheduledReport->deactivate();
            $message = 'Scheduled report disabled successfully.';
        } else {
            $scheduledReport->activate();
            $message = 'Scheduled report enabled successfully.';
        }
        
        return redirect()
            ->back()
            ->with('success', $message);
    }

    /**
     * Download a generated report.
     * Requirements: 17.4
     */
    public function download(GeneratedReport $generatedReport): Response
    {
        // Ensure user can only download their own reports
        $this->authorize('view', $generatedReport->scheduledReport);
        
        if (!$generatedReport->fileExists()) {
            abort(404, 'Report file not found.');
        }
        
        $filePath = Storage::path($generatedReport->file_path);
        $fileName = $generatedReport->getFileName();
        
        return response()->download($filePath, $fileName);
    }

    /**
     * Delete a generated report.
     * Requirements: 17.4
     */
    public function deleteGenerated(GeneratedReport $generatedReport): RedirectResponse
    {
        // Ensure user can only delete their own reports
        $this->authorize('delete', $generatedReport->scheduledReport);
        
        $generatedReport->delete();
        
        return redirect()
            ->back()
            ->with('success', 'Generated report deleted successfully.');
    }
}
