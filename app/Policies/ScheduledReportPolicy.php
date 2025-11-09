<?php

namespace App\Policies;

use App\Models\ScheduledReport;
use App\Models\User;

class ScheduledReportPolicy
{
    /**
     * Determine if the user can view any scheduled reports.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    /**
     * Determine if the user can view the scheduled report.
     */
    public function view(User $user, ScheduledReport $scheduledReport): bool
    {
        return $user->hasRole('administrator') && $user->id === $scheduledReport->user_id;
    }

    /**
     * Determine if the user can create scheduled reports.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('administrator');
    }

    /**
     * Determine if the user can update the scheduled report.
     */
    public function update(User $user, ScheduledReport $scheduledReport): bool
    {
        return $user->hasRole('administrator') && $user->id === $scheduledReport->user_id;
    }

    /**
     * Determine if the user can delete the scheduled report.
     */
    public function delete(User $user, ScheduledReport $scheduledReport): bool
    {
        return $user->hasRole('administrator') && $user->id === $scheduledReport->user_id;
    }
}
