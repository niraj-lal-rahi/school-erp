<?php

namespace App\Policies\HR;

use App\Models\HR\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hr.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->school_id === $department->school_id && $user->hasPermission('hr.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hr.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->school_id === $department->school_id && $user->hasPermission('hr.manage');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->school_id === $department->school_id && $user->hasPermission('hr.manage');
    }
}
