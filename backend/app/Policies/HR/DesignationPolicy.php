<?php

namespace App\Policies\HR;

use App\Models\HR\Designation;
use App\Models\User;

class DesignationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hr.view');
    }

    public function view(User $user, Designation $designation): bool
    {
        return $user->school_id === $designation->school_id && $user->hasPermission('hr.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hr.manage');
    }

    public function update(User $user, Designation $designation): bool
    {
        return $user->school_id === $designation->school_id && $user->hasPermission('hr.manage');
    }

    public function delete(User $user, Designation $designation): bool
    {
        return $user->school_id === $designation->school_id && $user->hasPermission('hr.manage');
    }
}
