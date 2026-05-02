<?php

namespace App\Services\Rbac;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\Rbac\RoleRepositoryInterface;
use App\Repositories\Contracts\Rbac\UserRoleRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoleAssignmentService
{
    public function __construct(
        protected UserRoleRepositoryInterface $userRoles,
        protected RoleRepositoryInterface $roles,
        protected AccessControlService $accessControl,
        protected RoleAuditService $audit,
    ) {
    }

    public function listUserRoles(User $user): Collection
    {
        return $this->userRoles->forUser($user->id, $user->school_id);
    }

    public function assignRole(User $user, Role $role, User $performedBy, ?string $ipAddress = null)
    {
        $this->guardRoleAssignment($user, $role, $performedBy);

        if ($this->userRoles->findByUserAndRole($user->id, $role->id, $role->school_id ?? $user->school_id)) {
            throw ValidationException::withMessages([
                'role_id' => ['The selected role is already assigned to this user.'],
            ]);
        }

        return DB::transaction(function () use ($user, $role, $performedBy, $ipAddress) {
            $assignment = $this->userRoles->create([
                'school_id' => $role->school_id ?? $user->school_id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'assigned_by' => $performedBy->id,
            ]);

            $this->audit->log('user_role.assigned', $role, $user, [], $assignment->toArray(), $performedBy, $ipAddress);
            $this->accessControl->clearUserCache($user);

            return $assignment;
        });
    }

    public function removeRole(User $user, Role $role, User $performedBy, ?string $ipAddress = null): void
    {
        $this->guardRoleAssignment($user, $role, $performedBy);

        $assignment = $this->userRoles->findByUserAndRole($user->id, $role->id, $role->school_id ?? $user->school_id);

        if (! $assignment) {
            throw ValidationException::withMessages([
                'role_id' => ['The selected role is not assigned to this user.'],
            ]);
        }

        DB::transaction(function () use ($assignment, $user, $role, $performedBy, $ipAddress): void {
            $oldValues = $assignment->toArray();
            $this->userRoles->delete($assignment);
            $this->audit->log('user_role.removed', $role, $user, $oldValues, [], $performedBy, $ipAddress);
            $this->accessControl->clearUserCache($user);
        });
    }

    protected function guardRoleAssignment(User $targetUser, Role $role, User $performedBy): void
    {
        if (($role->role_type === 'system' || $role->school_id === null) && ! $this->accessControl->isSuperAdmin($performedBy)) {
            throw ValidationException::withMessages([
                'role_id' => ['Only a super admin can assign a system role.'],
            ]);
        }

        if ($role->school_id !== null && $role->school_id !== $performedBy->school_id && ! $this->accessControl->isSuperAdmin($performedBy)) {
            throw ValidationException::withMessages([
                'role_id' => ['You cannot assign a role from another tenant.'],
            ]);
        }

        if ($targetUser->school_id !== $performedBy->school_id && ! $this->accessControl->isSuperAdmin($performedBy)) {
            throw ValidationException::withMessages([
                'user_id' => ['You cannot manage roles for a user from another tenant.'],
            ]);
        }
    }
}
