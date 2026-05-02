<?php

namespace App\Services\Rbac;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\Rbac\PermissionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RolePermissionService
{
    public function __construct(
        protected PermissionRepositoryInterface $permissions,
        protected AccessControlService $accessControl,
        protected RoleAuditService $audit,
    ) {
    }

    public function syncPermissions(Role $role, array $permissionIds, User $performedBy, ?string $ipAddress = null): Role
    {
        if (($role->role_type === 'system' || $role->school_id === null) && ! $this->accessControl->isSuperAdmin($performedBy)) {
            throw ValidationException::withMessages([
                'role' => ['Only a super admin can modify permissions for a system role.'],
            ]);
        }

        return DB::transaction(function () use ($role, $permissionIds, $performedBy, $ipAddress): Role {
            $oldPermissionIds = $role->permissions()->pluck('permissions.id')->all();

            $validIds = $this->permissions->all()
                ->whereIn('id', $permissionIds)
                ->pluck('id')
                ->all();

            $role->permissions()->sync($validIds);
            $role = $role->fresh(['permissions']);

            $this->audit->log(
                'role.permissions_synced',
                $role,
                null,
                ['permission_ids' => $oldPermissionIds],
                ['permission_ids' => $validIds],
                $performedBy,
                $ipAddress,
            );

            $this->accessControl->clearUsersCacheByRole($role);

            return $role;
        });
    }
}
