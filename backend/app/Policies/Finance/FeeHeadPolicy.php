<?php

namespace App\Policies\Finance;

use App\Models\Finance\FeeHead;
use App\Models\User;

class FeeHeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FeeHead $feeHead): bool
    {
        return $user->school_id === $feeHead->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FeeHead $feeHead): bool
    {
        return $user->school_id === $feeHead->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FeeHead $feeHead): bool
    {
        return $user->school_id === $feeHead->school_id && $user->hasPermission('finance.manage');
    }
}
