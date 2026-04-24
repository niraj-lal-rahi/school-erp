<?php

namespace App\Policies\Finance;

use App\Models\Finance\StudentFeeAssignment;
use App\Models\User;

class StudentFeeAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, StudentFeeAssignment $assignment): bool
    {
        return $user->school_id === $assignment->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, StudentFeeAssignment $assignment): bool
    {
        return $user->school_id === $assignment->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, StudentFeeAssignment $assignment): bool
    {
        return $user->school_id === $assignment->school_id && $user->hasPermission('finance.manage');
    }
}
