<?php

namespace App\Policies\Communication;

use App\Models\Communication\NotificationLog;
use App\Models\User;

class NotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, NotificationLog $notificationLog): bool
    {
        return $user->school_id === $notificationLog->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, NotificationLog $notificationLog): bool
    {
        return $user->school_id === $notificationLog->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, NotificationLog $notificationLog): bool
    {
        return $user->school_id === $notificationLog->school_id && $user->hasPermission('communication.manage');
    }

    public function managePreferences(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }
}
