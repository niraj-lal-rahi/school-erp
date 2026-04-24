<?php

namespace App\Policies\Finance;

use App\Models\Finance\FeeCategory;
use App\Models\User;

class FeeCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FeeCategory $feeCategory): bool
    {
        return $user->school_id === $feeCategory->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FeeCategory $feeCategory): bool
    {
        return $user->school_id === $feeCategory->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FeeCategory $feeCategory): bool
    {
        return $user->school_id === $feeCategory->school_id && $user->hasPermission('finance.manage');
    }
}
