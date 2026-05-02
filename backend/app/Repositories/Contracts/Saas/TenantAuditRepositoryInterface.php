<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\TenantAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TenantAuditRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function create(array $attributes): TenantAuditLog;
}
