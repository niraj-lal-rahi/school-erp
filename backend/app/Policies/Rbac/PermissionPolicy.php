<?php

namespace App\Policies\Rbac;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\User;

class PermissionPolicy
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

    public function view(User $user, Permission|PermissionGroup $resource): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Permission|PermissionGroup $resource): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, Permission|PermissionGroup $resource): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function sync(User $user): bool
    {
        return $this->isSuperAdmin($user) || $user->hasPermission('rbac.manage');
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
}
