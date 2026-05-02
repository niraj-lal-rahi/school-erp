<?php

namespace App\Policies\Rbac;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $user->hasPermission('rbac.view')
            || $user->hasPermission('rbac.manage');
    }

    public function viewOwn(User $user): bool
    {
        return true;
    }

    public function view(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (! ($user->hasPermission('rbac.view') || $user->hasPermission('rbac.manage'))) {
            return false;
        }

        if ($this->isSystemRole($role)) {
            return false;
        }

        return (int) $user->school_id === (int) $role->school_id;
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $user->hasPermission('rbac.manage');
    }

    public function update(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (! $user->hasPermission('rbac.manage')) {
            return false;
        }

        if ($this->isSystemRole($role)) {
            return false;
        }

        return (int) $user->school_id === (int) $role->school_id;
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->update($user, $role);
    }

    public function assignPermissions(User $user, Role $role): bool
    {
        return $this->update($user, $role);
    }

    public function clone(User $user, Role $role): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (! $user->hasPermission('rbac.manage')) {
            return false;
        }

        if ($this->isSystemRole($role)) {
            return false;
        }

        return (int) $user->school_id === (int) $role->school_id;
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

    protected function isSystemRole(Role $role): bool
    {
        return $role->role_type === 'system' || $role->school_id === null;
    }
}
