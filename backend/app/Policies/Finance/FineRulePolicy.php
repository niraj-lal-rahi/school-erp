<?php

namespace App\Policies\Finance;

use App\Models\Finance\FineRule;
use App\Models\User;

class FineRulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FineRule $fineRule): bool
    {
        return $user->school_id === $fineRule->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FineRule $fineRule): bool
    {
        return $user->school_id === $fineRule->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FineRule $fineRule): bool
    {
        return $user->school_id === $fineRule->school_id && $user->hasPermission('finance.manage');
    }
}
