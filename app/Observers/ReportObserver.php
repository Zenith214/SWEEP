<?php

namespace App\Observers;

use App\Models\Report;
use App\Services\CacheConfigService;

class ReportObserver
{
    /**
     * Handle the Report "created" event.
     */
    public function created(Report $report): void
    {
        CacheConfigService::invalidateReportMetrics();
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        CacheConfigService::invalidateReportMetrics();
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        CacheConfigService::invalidateReportMetrics();
    }
}
