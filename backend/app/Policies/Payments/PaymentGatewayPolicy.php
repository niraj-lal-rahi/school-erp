<?php

namespace App\Policies\Payments;

use App\Models\Payments\PaymentGateway;
use App\Models\User;

class PaymentGatewayPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isTenantAdmin($user);
    }

    public function view(User $user, PaymentGateway $gateway): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isTenantAdmin($user)
            && ($gateway->school_id === null || (int) $gateway->school_id === (int) $user->school_id);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isTenantAdmin($user);
    }

    public function update(User $user, PaymentGateway $gateway): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isTenantAdmin($user)
            && $gateway->school_id !== null
            && (int) $gateway->school_id === (int) $user->school_id;
    }

    public function delete(User $user, PaymentGateway $gateway): bool
    {
        return $this->update($user, $gateway);
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
