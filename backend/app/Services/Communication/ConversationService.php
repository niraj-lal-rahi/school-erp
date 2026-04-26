<?php

namespace App\Services\Communication;

use App\Models\Communication\CommunicationConversation;
use App\Models\Communication\CommunicationMessage;
use App\Models\Communication\ConversationParticipant;
use App\Repositories\Contracts\Communication\CommunicationMessageRepositoryInterface;
use App\Repositories\Contracts\Communication\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ConversationService
{
    public function __construct(
        protected ConversationRepositoryInterface $conversations,
        protected CommunicationMessageRepositoryInterface $messages,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->conversations->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): CommunicationConversation
    {
        return $this->conversations->findOrFail($id);
    }

    public function create(array $attributes): CommunicationConversation
    {
        return DB::transaction(function () use ($attributes): CommunicationConversation {
            $conversation = $this->conversations->create([
                'school_id' => $attributes['school_id'],
                'conversation_type' => $attributes['conversation_type'],
                'title' => $attributes['title'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
                'status' => $attributes['status'] ?? 'active',
            ]);

            foreach ($attributes['participants'] ?? [] as $participant) {
                $conversation->participants()->create([
                    'school_id' => $conversation->school_id,
                    'participant_type' => $participant['participant_type'],
                    'participant_id' => $participant['participant_id'],
                    'joined_at' => $participant['joined_at'] ?? now(),
                    'is_muted' => (bool) ($participant['is_muted'] ?? false),
                ]);
            }

            return $this->conversations->findOrFail($conversation->id);
        });
    }

    public function update(CommunicationConversation $conversation, array $attributes): CommunicationConversation
    {
        return DB::transaction(fn (): CommunicationConversation => $this->conversations->update($conversation, $attributes));
    }

    public function delete(CommunicationConversation $conversation): void
    {
        DB::transaction(function () use ($conversation): void {
            $this->conversations->delete($conversation);
        });
    }

    public function addParticipant(CommunicationConversation $conversation, array $attributes): ConversationParticipant
    {
        return DB::transaction(fn (): ConversationParticipant => $conversation->participants()->create([
            'school_id' => $conversation->school_id,
            'participant_type' => $attributes['participant_type'],
            'participant_id' => $attributes['participant_id'],
            'joined_at' => $attributes['joined_at'] ?? now(),
            'is_muted' => (bool) ($attributes['is_muted'] ?? false),
        ]));
    }

    public function removeParticipant(CommunicationConversation $conversation, int $participantId): void
    {
        DB::transaction(function () use ($conversation, $participantId): void {
            $conversation->participants()->whereKey($participantId)->delete();
        });
    }

    public function archive(CommunicationConversation $conversation): CommunicationConversation
    {
        return DB::transaction(fn (): CommunicationConversation => $this->conversations->update($conversation, [
            'status' => 'archived',
        ]));
    }

    public function close(CommunicationConversation $conversation): CommunicationConversation
    {
        return DB::transaction(fn (): CommunicationConversation => $this->conversations->update($conversation, [
            'status' => 'closed',
        ]));
    }

    public function messages(CommunicationConversation $conversation): Collection
    {
        return CommunicationMessage::query()
            ->where('conversation_id', $conversation->id)
            ->with([
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
            ])
            ->latest('id')
            ->get();
    }
}
