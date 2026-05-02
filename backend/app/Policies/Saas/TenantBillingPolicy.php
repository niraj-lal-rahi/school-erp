<?php

namespace App\Policies\Saas;

use App\Models\Saas\TenantBillingRecord;
use App\Models\User;

class TenantBillingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isTenantAdmin($user);
    }

    public function view(User $user, TenantBillingRecord $billingRecord): bool
    {
        return $this->isSuperAdmin($user)
            || ($this->isTenantAdmin($user) && (int) $user->school_id === (int) $billingRecord->school_id);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, TenantBillingRecord $billingRecord): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, TenantBillingRecord $billingRecord): bool
    {
        return $this->isSuperAdmin($user);
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
