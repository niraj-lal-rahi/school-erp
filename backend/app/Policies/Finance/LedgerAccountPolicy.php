<?php

namespace App\Policies\Finance;

use App\Models\Finance\LedgerAccount;
use App\Models\User;

class LedgerAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->school_id === $ledgerAccount->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->school_id === $ledgerAccount->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, LedgerAccount $ledgerAccount): bool
    {
        return $user->school_id === $ledgerAccount->school_id && $user->hasPermission('finance.manage');
    }
}
