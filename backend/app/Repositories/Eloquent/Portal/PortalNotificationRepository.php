<?php

namespace App\Repositories\Eloquent\Portal;

use App\Models\Portal\PortalNotification;
use App\Repositories\Contracts\Portal\PortalNotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PortalNotificationRepository implements PortalNotificationRepositoryInterface
{
    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('user_id', $userId)
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $value) => $query->where('student_id', $value))
            ->when($filters['notification_type'] ?? null, fn (Builder $query, string $value) => $query->where('notification_type', $value))
            ->when(array_key_exists('is_read', $filters), fn (Builder $query) => $query->where('is_read', (bool) $filters['is_read']))
            ->latest('id')
            ->paginate($perPage);
    }

    public function unreadCount(int $userId): int
    {
        return PortalNotification::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function findOrFail(int $id): PortalNotification
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): PortalNotification
    {
        $portalNotification = PortalNotification::create($attributes);

        return $this->findOrFail($portalNotification->id);
    }

    public function update(PortalNotification $portalNotification, array $attributes): PortalNotification
    {
        $portalNotification->update($attributes);

        return $this->findOrFail($portalNotification->id);
    }

    public function delete(PortalNotification $portalNotification): void
    {
        $portalNotification->delete();
    }

    protected function query(): Builder
    {
        return PortalNotification::query()
            ->with(['user', 'student']);
    }
}
