<?php

namespace App\Policies\Finance;

use App\Models\Finance\FeeInstallment;
use App\Models\User;

class FeeInstallmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FeeInstallment $feeInstallment): bool
    {
        return $user->school_id === $feeInstallment->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FeeInstallment $feeInstallment): bool
    {
        return $user->school_id === $feeInstallment->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FeeInstallment $feeInstallment): bool
    {
        return $user->school_id === $feeInstallment->school_id && $user->hasPermission('finance.manage');
    }
}
