<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\NotificationLog;
use App\Repositories\Contracts\Communication\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $notificationQuery) use ($search): void {
                    $notificationQuery->where('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('provider_message_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['channel'] ?? null, fn (Builder $query, string $value) => $query->where('channel', $value))
            ->when($filters['recipient_type'] ?? null, fn (Builder $query, string $value) => $query->where('notifiable_type', $value))
            ->when($filters['recipient_id'] ?? null, fn (Builder $query, $value) => $query->where('notifiable_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): NotificationLog
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): NotificationLog
    {
        $notificationLog = NotificationLog::create($attributes);

        return $this->findOrFail($notificationLog->id);
    }

    public function update(NotificationLog $notificationLog, array $attributes): NotificationLog
    {
        $notificationLog->update($attributes);

        return $this->findOrFail($notificationLog->id);
    }

    public function delete(NotificationLog $notificationLog): void
    {
        $notificationLog->delete();
    }

    protected function query(): Builder
    {
        return NotificationLog::query()->with([
            'template',
            'notifiableStudent',
            'notifiableGuardian',
            'notifiableStaff',
            'notifiableUser',
        ]);
    }
}
