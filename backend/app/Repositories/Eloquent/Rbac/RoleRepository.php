<?php

namespace App\Repositories\Eloquent\Rbac;

use App\Models\Role;
use App\Repositories\Contracts\Rbac\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->query($filters)
            ->latest('id')
            ->get();
    }

    public function findOrFail(int $id): Role
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(array $attributes): Role
    {
        $role = Role::withoutGlobalScopes()->create($attributes);

        return $this->findOrFail($role->id);
    }

    public function update(Role $role, array $attributes): Role
    {
        $role->update($attributes);

        return $this->findOrFail($role->id);
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    protected function query(array $filters = []): Builder
    {
        return $this->baseQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $roleQuery) use ($search): void {
                    $roleQuery->where('roles.name', 'like', "%{$search}%")
                        ->orWhere('roles.code', 'like', "%{$search}%")
                        ->orWhere('roles.slug', 'like', "%{$search}%")
                        ->orWhere('roles.description', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_type'] ?? null, fn (Builder $query, string $value) => $query->where('roles.role_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('roles.status', $value));
    }

    protected function baseQuery(): Builder
    {
        $schoolId = auth()->user()?->school_id;

        return Role::withoutGlobalScopes()
            ->visibleInTenant($schoolId)
            ->withCount(['permissions', 'users']);
    }
}
