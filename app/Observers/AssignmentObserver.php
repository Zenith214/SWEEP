<?php

namespace App\Observers;

use App\Models\Assignment;
use App\Services\CacheConfigService;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateFleetMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }

    /**
     * Handle the Assignment "updated" event.
     */
    public function updated(Assignment $assignment): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateFleetMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }

    /**
     * Handle the Assignment "deleted" event.
     */
    public function deleted(Assignment $assignment): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateFleetMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }
}
