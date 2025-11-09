<?php

namespace App\Observers;

use App\Models\RecyclingLog;
use App\Services\CacheConfigService;

class RecyclingLogObserver
{
    /**
     * Handle the RecyclingLog "created" event.
     */
    public function created(RecyclingLog $recyclingLog): void
    {
        CacheConfigService::invalidateRecyclingMetrics();
    }

    /**
     * Handle the RecyclingLog "updated" event.
     */
    public function updated(RecyclingLog $recyclingLog): void
    {
        CacheConfigService::invalidateRecyclingMetrics();
    }

    /**
     * Handle the RecyclingLog "deleted" event.
     */
    public function deleted(RecyclingLog $recyclingLog): void
    {
        CacheConfigService::invalidateRecyclingMetrics();
    }
}
