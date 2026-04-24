<?php

namespace App\Policies\Finance;

use App\Models\Finance\ExpenseCategory;
use App\Models\User;

class ExpenseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function view(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->school_id === $expenseCategory->school_id && $user->hasPermission('finance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function update(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->school_id === $expenseCategory->school_id && $user->hasPermission('finance.manage');
    }

    public function delete(User $user, ExpenseCategory $expenseCategory): bool
    {
        return $user->school_id === $expenseCategory->school_id && $user->hasPermission('finance.manage');
    }
}
