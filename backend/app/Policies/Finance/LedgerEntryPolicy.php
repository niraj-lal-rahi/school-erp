<?php

namespace App\Policies\Finance;

use App\Models\Finance\LedgerEntry;
use App\Models\User;

class LedgerEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $user->school_id === $ledgerEntry->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $user->school_id === $ledgerEntry->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, LedgerEntry $ledgerEntry): bool
    {
        return $user->school_id === $ledgerEntry->school_id && $user->hasPermission('finance.manage');
    }
}
