<?php

namespace App\Policies\Workflows;

use App\Models\User;
use App\Models\Workflows\ReminderLog;
use App\Models\Workflows\ReminderRule;

class ReminderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, ReminderRule|ReminderLog $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, ReminderRule|ReminderLog $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function delete(User $user, ReminderRule|ReminderLog $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function process(User $user, mixed $resource = null): bool
    {
        return $this->canManage($user);
    }

    protected function canView(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('workflows.view')
            || $user->hasPermission('workflows.manage');
    }

    protected function canManage(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('workflows.manage');
    }

    protected function sameTenant(User $user, ?int $schoolId): bool
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
