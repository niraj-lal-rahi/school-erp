<?php

namespace App\Policies\Saas;

use App\Models\Saas\TenantSubscription;
use App\Models\User;

class TenantSubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isTenantAdmin($user);
    }

    public function view(User $user, TenantSubscription $subscription): bool
    {
        return $this->isSuperAdmin($user)
            || ($this->isTenantAdmin($user) && (int) $user->school_id === (int) $subscription->school_id);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, TenantSubscription $subscription): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, TenantSubscription $subscription): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function changePlan(User $user, TenantSubscription $subscription): bool
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
