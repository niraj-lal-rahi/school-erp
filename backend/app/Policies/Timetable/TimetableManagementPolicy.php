<?php

namespace App\Policies\Timetable;

use App\Models\User;

class TimetableManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('timetable.view');
    }

    public function view(User $user, object $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('timetable.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('timetable.manage');
    }

    public function update(User $user, object $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('timetable.manage');
    }

    public function delete(User $user, object $model): bool
    {
        return $user->school_id === $model->school_id && $user->hasPermission('timetable.manage');
    }
}
