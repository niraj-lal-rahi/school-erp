<?php

namespace App\Policies;

use App\Models\Guardian;
use App\Models\User;

class GuardianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function view(User $user, Guardian $guardian): bool
    {
        return $user->school_id === $guardian->school_id && $user->hasPermission('students.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('students.create');
    }

    public function update(User $user, Guardian $guardian): bool
    {
        return $user->school_id === $guardian->school_id && $user->hasPermission('students.update');
    }

    public function delete(User $user, Guardian $guardian): bool
    {
        return $user->school_id === $guardian->school_id && $user->hasPermission('students.delete');
    }
}
