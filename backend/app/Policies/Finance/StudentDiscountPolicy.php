<?php

namespace App\Policies\Finance;

use App\Models\Finance\StudentDiscount;
use App\Models\User;

class StudentDiscountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, StudentDiscount $studentDiscount): bool
    {
        return $user->school_id === $studentDiscount->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, StudentDiscount $studentDiscount): bool
    {
        return $user->school_id === $studentDiscount->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, StudentDiscount $studentDiscount): bool
    {
        return $user->school_id === $studentDiscount->school_id && $user->hasPermission('finance.manage');
    }
}
