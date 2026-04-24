<?php

namespace App\Policies\Finance;

use App\Models\Finance\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, Receipt $receipt): bool
    {
        return $user->school_id === $receipt->school_id && $user->hasPermission('finance.view');
    }
}
