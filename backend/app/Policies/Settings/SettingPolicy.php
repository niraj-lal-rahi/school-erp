<?php

namespace App\Policies\Settings;

use App\Models\Settings\Setting;
use App\Models\Settings\SettingAuditLog;
use App\Models\Settings\SettingGroup;
use App\Models\Settings\LocalizationSetting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, Setting|SettingGroup|LocalizationSetting|SettingAuditLog $resource): bool
    {
        return $this->inScope($user, $resource->school_id ?? null) && $this->canView($user);
    }

    public function create(User $user, array $attributes = []): bool
    {
        return $this->inScope($user, $attributes['school_id'] ?? $user->school_id) && $this->canManage($user);
    }

    public function update(User $user, Setting|SettingGroup|LocalizationSetting $resource): bool
    {
        return $this->inScope($user, $resource->school_id ?? null) && $this->canManage($user);
    }

    public function delete(User $user, Setting|SettingGroup $resource): bool
    {
        return $this->update($user, $resource);
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

    protected function inScope(User $user, ?int $schoolId): bool
    {
        if ($schoolId === null) {
            return $this->isSuperAdmin($user);
        }

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
