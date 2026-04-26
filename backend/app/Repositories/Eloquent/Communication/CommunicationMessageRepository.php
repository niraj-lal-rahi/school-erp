<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\CommunicationMessage;
use App\Repositories\Contracts\Communication\CommunicationMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CommunicationMessageRepository implements CommunicationMessageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $messageQuery) use ($search): void {
                    $messageQuery->where('subject', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $value) => $query->where('priority', $value))
            ->when($filters['recipient_type'] ?? null, fn (Builder $query, string $value) => $query->where('recipient_type', $value))
            ->when($filters['recipient_id'] ?? null, fn (Builder $query, $value) => $query->where('recipient_id', $value))
            ->when($filters['channel'] ?? null, function (Builder $query, string $value): void {
                if ($value === 'in_app') {
                    $query->whereIn('message_type', ['direct', 'group', 'notification', 'system']);
                }
            })
            ->when($filters['conversation_id'] ?? null, fn (Builder $query, $value) => $query->where('conversation_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): CommunicationMessage
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): CommunicationMessage
    {
        $message = CommunicationMessage::create($attributes);

        return $this->findOrFail($message->id);
    }

    public function update(CommunicationMessage $message, array $attributes): CommunicationMessage
    {
        $message->update($attributes);

        return $this->findOrFail($message->id);
    }

    public function delete(CommunicationMessage $message): void
    {
        $message->delete();
    }

    protected function query(): Builder
    {
        return CommunicationMessage::query()
            ->with([
                'conversation',
                'attachments',
                'senderStudent',
                'senderGuardian',
                'senderStaff',
                'senderUser',
                'recipientStudent',
                'recipientGuardian',
                'recipientStaff',
                'recipientUser',
                'recipientGroup',
            ]);
    }
}
