<?php

namespace App\Services\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\Rbac\PermissionRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AccessControlService
{
    public function __construct(
        protected PermissionRepositoryInterface $permissions,
    ) {
    }

    public function checkPermission(User $user, string $permissionCode): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->resolvePermissionCodes($user)->contains($permissionCode);
    }

    public function checkRole(User $user, string $roleCode): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->resolveRoles($user)
            ->contains(fn (Role $role) => $role->code === $roleCode || $role->slug === $roleCode);
    }

    public function isSuperAdmin(User $user): bool
    {
        return $this->resolveRoles($user)
            ->contains(fn (Role $role) => ($role->code === 'super_admin' || $role->slug === 'super_admin'));
    }

    public function resolvePermissions(User $user): Collection
    {
        return Cache::remember(
            $this->permissionsCacheKey($user),
            now()->addMinutes(30),
            fn () => $this->resolveRoles($user)
                ->flatMap(fn (Role $role) => $role->permissions)
                ->unique('id')
                ->values()
        );
    }

    public function resolvePermissionCodes(User $user): Collection
    {
        return $this->resolvePermissions($user)
            ->pluck('code')
            ->unique()
            ->values();
    }

    public function resolveRoles(User $user): Collection
    {
        return Cache::remember(
            $this->rolesCacheKey($user),
            now()->addMinutes(30),
            fn () => $user->roles()
                ->with(['permissions'])
                ->visibleInTenant($user->school_id)
                ->where('roles.status', 'active')
                ->get()
        );
    }

    public function clearUserCache(User|int $user): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        Cache::forget("rbac:user:{$userId}:roles");
        Cache::forget("rbac:user:{$userId}:permissions");
    }

    public function clearUsersCacheByRole(Role $role): void
    {
        $role->loadMissing('users');

        foreach ($role->users as $user) {
            $this->clearUserCache($user);
        }
    }

    protected function permissionsCacheKey(User $user): string
    {
        return "rbac:user:{$user->id}:permissions";
    }

    protected function rolesCacheKey(User $user): string
    {
        return "rbac:user:{$user->id}:roles";
    }
}
