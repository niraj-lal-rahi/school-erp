<?php

namespace App\Services\Rbac;

use App\Models\Permission;
use App\Repositories\Contracts\Rbac\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PermissionService
{
    public function __construct(
        protected PermissionRepositoryInterface $permissions,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->permissions->paginate($filters, $perPage);
    }

    public function list(array $filters = []): Collection
    {
        return $this->permissions->all($filters);
    }

    public function groupedByModule(array $filters = []): Collection
    {
        return $this->permissions->all($filters)
            ->groupBy('module')
            ->map(fn (Collection $items) => $items->sortBy(['action', 'name'])->values())
            ->sortKeys();
    }

    public function findByCode(string $code): ?Permission
    {
        return $this->permissions->findByCode($code);
    }
}
