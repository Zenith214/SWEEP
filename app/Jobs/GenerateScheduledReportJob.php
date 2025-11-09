<?php

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Models\GeneratedReport;
use App\Services\DashboardService;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateScheduledReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ScheduledReport $scheduledReport
    ) {}

    /**
     * Execute the job.
     * Requirements: 17.4
     */
    public function handle(DashboardService $dashboardService, ExportService $exportService): void
    {
        try {
            Log::info('Starting scheduled report generation', [
                'scheduled_report_id' => $this->scheduledReport->id,
                'name' => $this->scheduledReport->name,
            ]);

            // Calculate the period for the report based on frequency
            [$periodStart, $periodEnd] = $this->calculateReportPeriod();

            // Build filters for the report
            $filters = [
                'start_date' => $periodStart->toDateString(),
                'end_date' => $periodEnd->toDateString(),
            ];

            // Get metrics data
            $metrics = $dashboardService->getAdminMetrics($filters);

            // Filter metrics based on selected metrics in scheduled report
            $filteredMetrics = $this->filterMetrics($metrics, $this->scheduledReport->metrics);

            // Generate the report file
            $filePath = $this->generateReportFile(
                $exportService,
                $filteredMetrics,
                $periodStart,
                $periodEnd
            );

            // Store the generated report record
            $generatedReport = GeneratedReport::create([
                'scheduled_report_id' => $this->scheduledReport->id,
                'file_path' => $filePath,
                'generated_at' => now(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'file_size' => Storage::size($filePath),
            ]);

            // Update the scheduled report's generation timestamps
            $this->scheduledReport->markAsGenerated();

            Log::info('Scheduled report generated successfully', [
                'scheduled_report_id' => $this->scheduledReport->id,
                'generated_report_id' => $generatedReport->id,
                'file_path' => $filePath,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate scheduled report', [
                'scheduled_report_id' => $this->scheduledReport->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Calculate the report period based on frequency.
     *
     * @return array{Carbon, Carbon}
     */
    protected function calculateReportPeriod(): array
    {
        $now = now();

        return match ($this->scheduledReport->frequency) {
            ScheduledReport::FREQUENCY_DAILY => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            ScheduledReport::FREQUENCY_WEEKLY => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            ScheduledReport::FREQUENCY_MONTHLY => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            default => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
        };
    }

    /**
     * Filter metrics based on selected metrics.
     */
    protected function filterMetrics(array $metrics, array $selectedMetrics): array
    {
        $filtered = [];

        foreach ($selectedMetrics as $metricKey) {
            if (isset($metrics[$metricKey])) {
                $filtered[$metricKey] = $metrics[$metricKey];
            }
        }

        // Always include metadata if present
        if (isset($metrics['metadata'])) {
            $filtered['metadata'] = $metrics['metadata'];
        }

        return $filtered;
    }

    /**
     * Generate the report file.
     */
    protected function generateReportFile(
        ExportService $exportService,
        array $metrics,
        Carbon $periodStart,
        Carbon $periodEnd
    ): string {
        $title = $this->scheduledReport->name . ' - ' . 
                 $periodStart->format('M d, Y') . ' to ' . 
                 $periodEnd->format('M d, Y');

        if ($this->scheduledReport->format === ScheduledReport::FORMAT_PDF) {
            return $exportService->exportToPDF($metrics, [], $title, true);
        } else {
            return $exportService->exportToCSV($metrics, true);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Scheduled report generation job failed permanently', [
            'scheduled_report_id' => $this->scheduledReport->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
