<?php

namespace App\Policies\Workflows;

use App\Models\User;
use App\Models\Workflows\AutomationRule;
use App\Models\Workflows\AutomationRun;

class AutomationPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, AutomationRule|AutomationRun $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, AutomationRule|AutomationRun $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function delete(User $user, AutomationRule|AutomationRun $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function run(User $user, AutomationRule $automationRule): bool
    {
        return $this->sameTenant($user, $automationRule->school_id) && $this->canManage($user);
    }

    public function activate(User $user, AutomationRule $automationRule): bool
    {
        return $this->update($user, $automationRule);
    }

    public function deactivate(User $user, AutomationRule $automationRule): bool
    {
        return $this->update($user, $automationRule);
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
