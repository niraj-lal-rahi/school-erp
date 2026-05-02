<?php

namespace App\Repositories\Eloquent\Rbac;

use App\Models\RoleAuditLog;
use App\Repositories\Contracts\Rbac\RoleAuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RoleAuditLogRepository implements RoleAuditLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $schoolId = auth()->user()?->school_id;

        return RoleAuditLog::query()
            ->with(['role', 'user', 'performedBy'])
            ->when($schoolId !== null, fn (Builder $query) => $query->where('role_audit_logs.school_id', $schoolId))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $auditQuery) use ($search): void {
                    $auditQuery->where('role_audit_logs.action', 'like', "%{$search}%")
                        ->orWhere('role_audit_logs.ip_address', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_id'] ?? null, fn (Builder $query, $value) => $query->where('role_audit_logs.role_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('role_audit_logs.created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('role_audit_logs.created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $attributes): RoleAuditLog
    {
        return RoleAuditLog::query()->create($attributes);
    }
}
