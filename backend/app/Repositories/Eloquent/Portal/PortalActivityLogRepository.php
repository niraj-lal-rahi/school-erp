<?php

namespace App\Repositories\Eloquent\Portal;

use App\Models\Portal\PortalActivityLog;
use App\Repositories\Contracts\Portal\PortalActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PortalActivityLogRepository implements PortalActivityLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['user_id'] ?? null, fn (Builder $query, int $value) => $query->where('user_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $value) => $query->where('student_id', $value))
            ->when($filters['action'] ?? null, fn (Builder $query, string $value) => $query->where('action', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(array $attributes): PortalActivityLog
    {
        return PortalActivityLog::create($attributes)->fresh(['user', 'student']);
    }

    protected function query(): Builder
    {
        return PortalActivityLog::query()
            ->with(['user', 'student']);
    }
}
