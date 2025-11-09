<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\CollectionLog;
use App\Models\RecyclingLog;
use App\Models\Report;
use App\Models\ScheduledReport;
use App\Observers\AssignmentObserver;
use App\Observers\CollectionLogObserver;
use App\Observers\RecyclingLogObserver;
use App\Observers\ReportObserver;
use App\Policies\RecyclingLogPolicy;
use App\Policies\ScheduledReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        RecyclingLog::class => RecyclingLogPolicy::class,
        ScheduledReport::class => ScheduledReportPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Register model observers for cache invalidation
        CollectionLog::observe(CollectionLogObserver::class);
        RecyclingLog::observe(RecyclingLogObserver::class);
        Report::observe(ReportObserver::class);
        Assignment::observe(AssignmentObserver::class);
    }
}
