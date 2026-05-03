<?php

namespace App\Policies\Settings;

use App\Models\Settings\BrandingSetting;
use App\Models\User;

class BrandingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, BrandingSetting $brandingSetting): bool
    {
        return $this->sameTenant($user, $brandingSetting->school_id) && $this->canView($user);
    }

    public function update(User $user, BrandingSetting $brandingSetting): bool
    {
        return $this->sameTenant($user, $brandingSetting->school_id) && $this->canManage($user);
    }

    public function updateTenant(User $user, int $schoolId): bool
    {
        return $this->sameTenant($user, $schoolId) && $this->canManage($user);
    }

    protected function canView(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('settings.view')
            || $user->hasPermission('settings.manage');
    }

    protected function canManage(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('settings.manage');
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
