<?php

namespace App\Policies\Finance;

use App\Models\Finance\DiscountType;
use App\Models\User;

class DiscountTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, DiscountType $discountType): bool
    {
        return $user->school_id === $discountType->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, DiscountType $discountType): bool
    {
        return $user->school_id === $discountType->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, DiscountType $discountType): bool
    {
        return $user->school_id === $discountType->school_id && $user->hasPermission('finance.manage');
    }
}
