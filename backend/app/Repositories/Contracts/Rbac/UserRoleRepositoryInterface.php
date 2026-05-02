<?php

namespace App\Repositories\Contracts\Rbac;

use App\Models\UserRole;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRoleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function forUser(int $userId, ?int $schoolId = null): Collection;

    public function findByUserAndRole(int $userId, int $roleId, ?int $schoolId = null): ?UserRole;

    public function create(array $attributes): UserRole;

    public function delete(UserRole $userRole): void;
}
