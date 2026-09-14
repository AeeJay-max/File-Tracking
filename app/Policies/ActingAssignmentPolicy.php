<?php

namespace App\Policies;

use App\Models\ActingAssignment;
use App\Models\User;

class ActingAssignmentPolicy
{
    /**
     * Determine if user can create an acting assignment.
     * Only Permanent Secretary can assign/delegate.
     */
    public function create(User $user): bool
    {
        return $user->designation?->name === 'Permanent Secretary' || $user->email === 'permsec@filetrack.local';
    }

    /**
     * Determine if user can view/action an acting assignment.
     */
    public function complete(User $user, ActingAssignment $assignment): bool
    {
        if ($user->role !== 'chief_director') {
            return false;
        }

        return (int) $assignment->assigned_to === $user->id && $assignment->status === 'active';
    }
}
