<?php

namespace App\Repositories\Contracts\Rbac;

use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PermissionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): Permission;

    public function findByCode(string $code): ?Permission;
}
