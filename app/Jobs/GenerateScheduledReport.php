<?php

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Models\GeneratedReport;
use App\Services\DashboardService;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateScheduledReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ScheduledReport $scheduledReport
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DashboardService $dashboardService, ExportService $exportService): void
    {
        try {
            Log::info("Generating scheduled report: {$this->scheduledReport->name} (ID: {$this->scheduledReport->id})");

            // Calculate period based on frequency
            $period = $this->calculatePeriod();

            // Get metrics data
            $metrics = $dashboardService->getAdminMetrics([
                'start_date' => $period['start'],
                'end_date' => $period['end'],
                'metrics' => $this->scheduledReport->metrics,
            ]);

            // Generate report file
            $filePath = $this->generateReportFile($exportService, $metrics, $period);

            // Store generated report record
            $generatedReport = GeneratedReport::create([
                'scheduled_report_id' => $this->scheduledReport->id,
                'file_path' => $filePath,
                'generated_at' => now(),
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'file_size' => Storage::size($filePath),
            ]);

            // Mark scheduled report as generated
            $this->scheduledReport->markAsGenerated();

            Log::info("Successfully generated report: {$generatedReport->file_path}");
        } catch (\Exception $e) {
            Log::error("Failed to generate scheduled report: {$this->scheduledReport->name}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate the reporting period based on frequency.
     */
    private function calculatePeriod(): array
    {
        $end = now()->subDay()->endOfDay();

        $start = match ($this->scheduledReport->frequency) {
            ScheduledReport::FREQUENCY_DAILY => $end->copy()->startOfDay(),
            ScheduledReport::FREQUENCY_WEEKLY => $end->copy()->subWeek()->startOfDay(),
            ScheduledReport::FREQUENCY_MONTHLY => $end->copy()->subMonth()->startOfDay(),
            default => $end->copy()->startOfDay(),
        };

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Generate the report file.
     */
    private function generateReportFile(ExportService $exportService, array $metrics, array $period): string
    {
        $filename = $this->generateFilename($period['start'], $period['end']);
        $directory = 'reports/scheduled/' . $this->scheduledReport->id;

        // Ensure directory exists
        Storage::makeDirectory($directory);

        $filePath = $directory . '/' . $filename;

        if ($this->scheduledReport->format === ScheduledReport::FORMAT_PDF) {
            $content = $exportService->exportToPDF($metrics, [
                'title' => $this->scheduledReport->name,
                'period_start' => $period['start'],
                'period_end' => $period['end'],
            ]);
            Storage::put($filePath, $content);
        } else {
            $content = $exportService->exportToCSV($metrics);
            Storage::put($filePath, $content);
        }

        return $filePath;
    }

    /**
     * Generate filename for the report.
     */
    private function generateFilename(Carbon $start, Carbon $end): string
    {
        $sanitizedName = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->scheduledReport->name);
        $dateRange = $start->format('Ymd') . '_' . $end->format('Ymd');
        $extension = $this->scheduledReport->format;

        return "{$sanitizedName}_{$dateRange}.{$extension}";
    }
}
