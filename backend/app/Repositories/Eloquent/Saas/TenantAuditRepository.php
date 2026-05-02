<?php

namespace App\Repositories\Eloquent\Saas;

use App\Models\Saas\TenantAuditLog;
use App\Repositories\Contracts\Saas\TenantAuditRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TenantAuditRepository implements TenantAuditRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TenantAuditLog::query()
            ->with(['tenant', 'performer'])
            ->when($filters['tenant_id'] ?? null, fn (Builder $query, $value) => $query->where('school_id', $value))
            ->when($filters['action'] ?? null, fn (Builder $query, string $value) => $query->where('action', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $attributes): TenantAuditLog
    {
        return TenantAuditLog::query()->create($attributes);
    }
}
