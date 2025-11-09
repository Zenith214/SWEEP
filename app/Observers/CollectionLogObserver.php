<?php

namespace App\Observers;

use App\Models\CollectionLog;
use App\Services\CacheConfigService;

class CollectionLogObserver
{
    /**
     * Handle the CollectionLog "created" event.
     */
    public function created(CollectionLog $collectionLog): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }

    /**
     * Handle the CollectionLog "updated" event.
     */
    public function updated(CollectionLog $collectionLog): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }

    /**
     * Handle the CollectionLog "deleted" event.
     */
    public function deleted(CollectionLog $collectionLog): void
    {
        CacheConfigService::invalidateCollectionMetrics();
        CacheConfigService::invalidateCrewMetrics();
        CacheConfigService::invalidateRouteMetrics();
    }
}
