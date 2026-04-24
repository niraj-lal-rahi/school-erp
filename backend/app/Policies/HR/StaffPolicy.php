<?php

namespace App\Policies\HR;

use App\Models\HR\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('hr.view');
    }

    public function view(User $user, Staff $staff): bool
    {
        return $user->school_id === $staff->school_id && $user->hasPermission('hr.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('hr.manage');
    }

    public function update(User $user, Staff $staff): bool
    {
        return $user->school_id === $staff->school_id && $user->hasPermission('hr.manage');
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $user->school_id === $staff->school_id && $user->hasPermission('hr.manage');
    }
}
