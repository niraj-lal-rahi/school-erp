<?php

namespace App\Repositories\Eloquent\Rbac;

use App\Models\Permission;
use App\Repositories\Contracts\Rbac\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->orderBy('module')
            ->orderBy('action')
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(array $filters = []): Collection
    {
        return $this->query($filters)
            ->orderBy('module')
            ->orderBy('action')
            ->orderBy('name')
            ->get();
    }

    public function findOrFail(int $id): Permission
    {
        return Permission::query()->findOrFail($id);
    }

    public function findByCode(string $code): ?Permission
    {
        return Permission::query()->where('code', $code)->first();
    }

    protected function query(array $filters = []): Builder
    {
        return Permission::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $permissionQuery) use ($search): void {
                    $permissionQuery->where('permissions.name', 'like', "%{$search}%")
                        ->orWhere('permissions.code', 'like', "%{$search}%")
                        ->orWhere('permissions.description', 'like', "%{$search}%");
                });
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('permissions.module', $value))
            ->when($filters['action'] ?? null, fn (Builder $query, string $value) => $query->where('permissions.action', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('permissions.status', $value));
    }
}
