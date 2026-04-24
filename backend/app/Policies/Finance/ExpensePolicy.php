<?php

namespace App\Policies\Finance;

use App\Models\Finance\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->school_id === $expense->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->school_id === $expense->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->school_id === $expense->school_id && $user->hasPermission('finance.manage');
    }
}
