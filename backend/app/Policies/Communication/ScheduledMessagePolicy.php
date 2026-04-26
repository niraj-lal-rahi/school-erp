<?php

namespace App\Policies\Communication;

use App\Models\Communication\ScheduledMessage;
use App\Models\User;

class ScheduledMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, ScheduledMessage $scheduledMessage): bool
    {
        return $user->school_id === $scheduledMessage->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, ScheduledMessage $scheduledMessage): bool
    {
        return $user->school_id === $scheduledMessage->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, ScheduledMessage $scheduledMessage): bool
    {
        return $user->school_id === $scheduledMessage->school_id && $user->hasPermission('communication.manage');
    }

    public function cancel(User $user, ScheduledMessage $scheduledMessage): bool
    {
        return $user->school_id === $scheduledMessage->school_id && $user->hasPermission('communication.manage');
    }
}
