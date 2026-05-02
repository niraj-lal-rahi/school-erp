<?php

namespace App\Repositories\Contracts\Rbac;

use App\Models\RoleAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoleAuditLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $attributes): RoleAuditLog;
}
