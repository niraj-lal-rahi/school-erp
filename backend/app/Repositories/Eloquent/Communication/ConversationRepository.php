<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\CommunicationConversation;
use App\Repositories\Contracts\Communication\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $conversationQuery) use ($search): void {
                    $conversationQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('conversation_type', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['recipient_type'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('participants', fn (Builder $participantQuery) => $participantQuery->where('participant_type', $value));
            })
            ->when($filters['recipient_id'] ?? null, function (Builder $query, $value): void {
                $query->whereHas('participants', fn (Builder $participantQuery) => $participantQuery->where('participant_id', $value));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): CommunicationConversation
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): CommunicationConversation
    {
        $conversation = CommunicationConversation::create($attributes);

        return $this->findOrFail($conversation->id);
    }

    public function update(CommunicationConversation $conversation, array $attributes): CommunicationConversation
    {
        $conversation->update($attributes);

        return $this->findOrFail($conversation->id);
    }

    public function delete(CommunicationConversation $conversation): void
    {
        $conversation->delete();
    }

    protected function query(): Builder
    {
        return CommunicationConversation::query()
            ->with([
                'creator',
                'participants',
            ])
            ->withCount([
                'participants',
                'messages',
            ]);
    }
}
