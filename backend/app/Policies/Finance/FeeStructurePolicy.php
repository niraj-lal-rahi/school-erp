<?php

namespace App\Policies\Finance;

use App\Models\Finance\FeeStructure;
use App\Models\User;

class FeeStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FeeStructure $feeStructure): bool
    {
        return $user->school_id === $feeStructure->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FeeStructure $feeStructure): bool
    {
        return $user->school_id === $feeStructure->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FeeStructure $feeStructure): bool
    {
        return $user->school_id === $feeStructure->school_id && $user->hasPermission('finance.manage');
    }
}
