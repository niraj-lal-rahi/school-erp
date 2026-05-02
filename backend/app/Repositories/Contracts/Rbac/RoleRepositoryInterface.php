<?php

namespace App\Repositories\Contracts\Rbac;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): Role;

    public function create(array $attributes): Role;

    public function update(Role $role, array $attributes): Role;

    public function delete(Role $role): void;
}
