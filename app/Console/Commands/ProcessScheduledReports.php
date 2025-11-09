<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Jobs\GenerateScheduledReportJob;
use Illuminate\Console\Command;

class ProcessScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled reports that are due for generation';

    /**
     * Execute the console command.
     * Requirements: 17.4
     */
    public function handle(): int
    {
        $this->info('Processing scheduled reports...');

        // Get all scheduled reports that are due for generation
        $dueReports = ScheduledReport::dueForGeneration()->get();

        if ($dueReports->isEmpty()) {
            $this->info('No scheduled reports due for generation.');
            return self::SUCCESS;
        }

        $this->info("Found {$dueReports->count()} report(s) due for generation.");

        foreach ($dueReports as $report) {
            $this->line("Dispatching job for: {$report->name} (ID: {$report->id})");
            
            // Dispatch the job to generate the report
            GenerateScheduledReportJob::dispatch($report);
        }

        $this->info('All scheduled report jobs have been dispatched.');

        return self::SUCCESS;
    }
}
