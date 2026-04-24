<?php

namespace App\Policies\Finance;

use App\Models\Finance\FeeInvoice;
use App\Models\User;

class FeeInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, FeeInvoice $feeInvoice): bool
    {
        return $user->school_id === $feeInvoice->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, FeeInvoice $feeInvoice): bool
    {
        return $user->school_id === $feeInvoice->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, FeeInvoice $feeInvoice): bool
    {
        return $user->school_id === $feeInvoice->school_id && $user->hasPermission('finance.manage');
    }
}
