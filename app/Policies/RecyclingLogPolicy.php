<?php

namespace App\Policies;

use App\Models\RecyclingLog;
use App\Models\User;

class RecyclingLogPolicy
{
    /**
     * Determine if the user can view the recycling log.
     * Crew members can view their own logs, administrators can view all logs.
     * 
     * Requirements: 5.1, 5.2
     */
    public function view(User $user, RecyclingLog $recyclingLog): bool
    {
        // Administrators can view all logs
        if ($user->hasRole('administrator')) {
            return true;
        }

        // Collection crew can view their own logs
        if ($user->hasRole('collection_crew')) {
            return $recyclingLog->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can create recycling logs.
     * Only collection crew members can create logs.
     * 
     * Requirements: 5.1, 5.2
     */
    public function create(User $user): bool
    {
        return $user->hasRole('collection_crew');
    }

    /**
     * Determine if the user can update the recycling log.
     * Must be the owner, within edit window, and have collection_crew role.
     * 
     * Requirements: 14.1, 14.2
     */
    public function update(User $user, RecyclingLog $recyclingLog): bool
    {
        // Must have collection_crew role
        if (!$user->hasRole('collection_crew')) {
            return false;
        }

        // Must be the owner
        if ($recyclingLog->user_id !== $user->id) {
            return false;
        }

        // Must be within edit window (2 hours)
        if (!$recyclingLog->isWithinEditWindow()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can delete the recycling log.
     * Only administrators can delete logs.
     * 
     * Requirements: 5.1, 6.1
     */
    public function delete(User $user, RecyclingLog $recyclingLog): bool
    {
        return $user->hasRole('administrator');
    }
}
