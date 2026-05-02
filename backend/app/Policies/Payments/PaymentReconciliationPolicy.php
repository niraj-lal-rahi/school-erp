<?php

namespace App\Policies\Payments;

use App\Models\Payments\PaymentReconciliation;
use App\Models\Payments\PaymentTransaction;
use App\Models\User;

class PaymentReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isFinanceOperator($user);
    }

    public function view(User $user, PaymentReconciliation $reconciliation): bool
    {
        return $this->sameTenant($user, $reconciliation->school_id) && $this->isFinanceOperator($user);
    }

    public function create(User $user, PaymentTransaction $transaction): bool
    {
        return $this->sameTenant($user, $transaction->school_id) && $this->isFinanceOperator($user);
    }

    protected function isFinanceOperator(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('finance.manage')
            || $user->roles()
                ->withoutGlobalScopes()
                ->where(function ($query): void {
                    $query->where('roles.code', 'accountant')
                        ->orWhere('roles.slug', 'accountant');
                })
                ->where(function ($query) use ($user): void {
                    $query->whereNull('roles.school_id')
                        ->orWhere('roles.school_id', $user->school_id);
                })
                ->exists();
    }

    protected function sameTenant(User $user, int $schoolId): bool
    {
        return (int) $user->school_id === (int) $schoolId;
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function isTenantAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'tenant_admin')
                    ->orWhere('roles.slug', 'school-admin');
            })
            ->where('roles.school_id', $user->school_id)
            ->exists();
    }
}
