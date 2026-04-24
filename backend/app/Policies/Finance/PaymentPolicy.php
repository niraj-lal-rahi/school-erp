<?php

namespace App\Policies\Finance;

use App\Models\Finance\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->school_id === $payment->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->school_id === $payment->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->school_id === $payment->school_id && $user->hasPermission('finance.manage');
    }
}
