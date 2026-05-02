<?php

namespace App\Policies\Rbac;

use App\Models\User;
use App\Models\UserRole;

class UserRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $user->hasPermission('rbac.view')
            || $user->hasPermission('rbac.manage');
    }

    public function view(User $user, UserRole $userRole): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($user->id === $userRole->user_id) {
            return true;
        }

        if (! ($user->hasPermission('rbac.view') || $user->hasPermission('rbac.manage'))) {
            return false;
        }

        return $userRole->school_id !== null && (int) $userRole->school_id === (int) $user->school_id;
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user) || $user->hasPermission('rbac.manage');
    }

    public function delete(User $user, UserRole $userRole): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if (! $user->hasPermission('rbac.manage')) {
            return false;
        }

        return $userRole->school_id !== null && (int) $userRole->school_id === (int) $user->school_id;
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
