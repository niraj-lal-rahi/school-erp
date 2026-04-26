<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\ScheduledMessage;
use App\Repositories\Contracts\Communication\ScheduledMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ScheduledMessageRepository implements ScheduledMessageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $scheduledQuery) use ($search): void {
                    $scheduledQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['audience_type'] ?? null, fn (Builder $query, string $value) => $query->where('audience_type', $value))
            ->when($filters['channel'] ?? null, fn (Builder $query, string $value) => $query->where('channel', $value))
            ->when($filters['class_id'] ?? null, fn (Builder $query, $value) => $query->where('class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, $value) => $query->where('section_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->where('scheduled_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->where('scheduled_at', '<=', $value))
            ->orderBy('scheduled_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ScheduledMessage
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ScheduledMessage
    {
        $scheduledMessage = ScheduledMessage::create($attributes);

        return $this->findOrFail($scheduledMessage->id);
    }

    public function update(ScheduledMessage $scheduledMessage, array $attributes): ScheduledMessage
    {
        $scheduledMessage->update($attributes);

        return $this->findOrFail($scheduledMessage->id);
    }

    public function delete(ScheduledMessage $scheduledMessage): void
    {
        $scheduledMessage->delete();
    }

    protected function query(): Builder
    {
        return ScheduledMessage::query()->with([
            'template',
            'schoolClass',
            'section',
            'creator',
        ]);
    }
}
