<?php

namespace App\Services\Rbac;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\Rbac\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected AccessControlService $accessControl,
        protected RoleAuditService $audit,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->roles->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Role
    {
        return $this->roles->findOrFail($id);
    }

    public function create(array $attributes, User $performedBy, ?string $ipAddress = null): Role
    {
        return DB::transaction(function () use ($attributes, $performedBy, $ipAddress): Role {
            $role = $this->roles->create([
                'school_id' => $attributes['role_type'] === 'system' ? null : ($attributes['school_id'] ?? $performedBy->school_id),
                'uuid' => $attributes['uuid'] ?? (string) Str::uuid(),
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'slug' => $attributes['slug'] ?? $attributes['code'],
                'scope' => $attributes['role_type'] ?? 'tenant',
                'description' => $attributes['description'] ?? null,
                'role_type' => $attributes['role_type'] ?? 'tenant',
                'is_default' => (bool) ($attributes['is_default'] ?? false),
                'status' => $attributes['status'] ?? 'active',
            ]);

            $this->audit->log('role.created', $role, null, [], $role->toArray(), $performedBy, $ipAddress);

            return $role;
        });
    }

    public function update(Role $role, array $attributes, User $performedBy, ?string $ipAddress = null): Role
    {
        $this->guardSystemRoleModification($role, $performedBy);

        return DB::transaction(function () use ($role, $attributes, $performedBy, $ipAddress): Role {
            $oldValues = $role->toArray();

            $updated = $this->roles->update($role, [
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'slug' => $attributes['slug'] ?? $attributes['code'],
                'description' => $attributes['description'] ?? null,
                'is_default' => (bool) ($attributes['is_default'] ?? $role->is_default),
                'status' => $attributes['status'] ?? $role->status,
            ]);

            $this->audit->log('role.updated', $updated, null, $oldValues, $updated->toArray(), $performedBy, $ipAddress);
            $this->accessControl->clearUsersCacheByRole($updated);

            return $updated;
        });
    }

    public function delete(Role $role, User $performedBy, ?string $ipAddress = null): void
    {
        $this->guardSystemRoleModification($role, $performedBy);

        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'The selected role is assigned to users and cannot be deleted yet.',
            ]);
        }

        DB::transaction(function () use ($role, $performedBy, $ipAddress): void {
            $oldValues = $role->toArray();

            $this->roles->delete($role);
            $this->audit->log('role.deleted', $role, null, $oldValues, [], $performedBy, $ipAddress);
        });
    }

    public function clone(Role $sourceRole, array $attributes, User $performedBy, ?string $ipAddress = null): Role
    {
        $this->guardSystemRoleModification($sourceRole, $performedBy, false);

        return DB::transaction(function () use ($sourceRole, $attributes, $performedBy, $ipAddress): Role {
            $clonedRole = $this->roles->create([
                'school_id' => $sourceRole->school_id ?? $performedBy->school_id,
                'uuid' => (string) Str::uuid(),
                'name' => $attributes['name'],
                'code' => $attributes['code'],
                'slug' => $attributes['code'],
                'scope' => $sourceRole->scope,
                'description' => $attributes['description'] ?? $sourceRole->description,
                'role_type' => $sourceRole->role_type,
                'is_default' => false,
                'status' => $sourceRole->status,
            ]);

            $clonedRole->permissions()->sync($sourceRole->permissions()->pluck('permissions.id')->all());

            $this->audit->log(
                'role.cloned',
                $clonedRole,
                null,
                ['source_role_id' => $sourceRole->id],
                $clonedRole->load('permissions')->toArray(),
                $performedBy,
                $ipAddress,
            );

            return $clonedRole->load(['permissions']);
        });
    }

    protected function guardSystemRoleModification(Role $role, User $performedBy, bool $strictDelete = true): void
    {
        $isSystemRole = ($role->role_type === 'system' || $role->school_id === null);

        if ($isSystemRole && ! $this->accessControl->isSuperAdmin($performedBy)) {
            throw ValidationException::withMessages([
                'role' => ['Only a super admin can modify a system role.'],
            ]);
        }

        if ($strictDelete && $role->code === 'super_admin') {
            throw ValidationException::withMessages([
                'role' => ['The super admin role cannot be deleted.'],
            ]);
        }
    }
}
