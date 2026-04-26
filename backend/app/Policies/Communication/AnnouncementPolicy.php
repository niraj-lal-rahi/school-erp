<?php

namespace App\Policies\Communication;

use App\Models\Communication\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('communication.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->school_id === $announcement->school_id && $user->hasPermission('communication.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->school_id === $announcement->school_id && $user->hasPermission('communication.manage');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->school_id === $announcement->school_id && $user->hasPermission('communication.manage');
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        return $user->school_id === $announcement->school_id && $user->hasPermission('communication.manage');
    }

    public function cancel(User $user, Announcement $announcement): bool
    {
        return $user->school_id === $announcement->school_id && $user->hasPermission('communication.manage');
    }
}
