<?php

namespace App\Policies\Finance;

use App\Models\Finance\Refund;
use App\Models\User;

class RefundPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, Refund $refund): bool
    {
        return $user->school_id === $refund->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, Refund $refund): bool
    {
        return $user->school_id === $refund->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, Refund $refund): bool
    {
        return $user->school_id === $refund->school_id && $user->hasPermission('finance.manage');
    }
}
