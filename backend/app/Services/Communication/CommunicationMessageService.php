<?php

namespace App\Services\Communication;

use App\Events\Communication\MessageSent;
use App\Models\Communication\CommunicationGroup;
use App\Models\Communication\CommunicationMessage;
use App\Models\Communication\MessageAttachment;
use App\Repositories\Contracts\Communication\CommunicationMessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommunicationMessageService
{
    public function __construct(
        protected CommunicationMessageRepositoryInterface $messages,
        protected RecipientResolverService $recipientResolver,
        protected NotificationService $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->messages->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): CommunicationMessage
    {
        return $this->messages->findOrFail($id);
    }

    public function sendDirectMessage(array $attributes): CommunicationMessage
    {
        return DB::transaction(function () use ($attributes): CommunicationMessage {
            $message = $this->messages->create($this->baseMessagePayload($attributes));

            $this->storeAttachments($message, $attributes['attachments_meta'] ?? [], $attributes['sender_user_id'] ?? null);
            $this->sendNotificationsForMessage($message, collect([[
                'recipient_type' => $message->recipient_type,
                'recipient_id' => $message->recipient_id,
                'recipient' => $message->resolveRecipient(),
            ]]), $attributes);
            event(new MessageSent($message->refresh()));

            return $this->messages->findOrFail($message->id);
        });
    }

    public function sendGroupMessage(array $attributes): CommunicationMessage
    {
        return DB::transaction(function () use ($attributes): CommunicationMessage {
            $group = CommunicationGroup::query()->findOrFail((int) $attributes['recipient_id']);
            $message = $this->messages->create($this->baseMessagePayload([
                ...$attributes,
                'recipient_type' => 'group',
                'message_type' => $attributes['message_type'] ?? 'group',
            ]));

            $this->storeAttachments($message, $attributes['attachments_meta'] ?? [], $attributes['sender_user_id'] ?? null);

            $recipients = $this->recipientResolver->resolveGroupMembers($group);
            $this->sendNotificationsForMessage($message, $recipients, $attributes);
            event(new MessageSent($message->refresh()));

            return $this->messages->findOrFail($message->id);
        });
    }

    public function markAsRead(CommunicationMessage $message): CommunicationMessage
    {
        return DB::transaction(fn (): CommunicationMessage => $this->messages->update($message, [
            'status' => 'read',
            'read_at' => now(),
        ]));
    }

    public function archive(CommunicationMessage $message): CommunicationMessage
    {
        return DB::transaction(fn (): CommunicationMessage => $this->messages->update($message, [
            'status' => 'archived',
        ]));
    }

    public function delete(CommunicationMessage $message): void
    {
        DB::transaction(function () use ($message): void {
            $this->messages->delete($message);
        });
    }

    protected function sendNotificationsForMessage(CommunicationMessage $message, Collection $recipients, array $attributes): void
    {
        $channels = $attributes['channels'] ?? null;
        if (is_string($channels)) {
            $channels = [$channels];
        }

        $this->notifications->sendToRecipients($recipients, [
            'school_id' => $message->school_id,
            'subject' => $message->subject,
            'message' => $message->body,
            'channels' => $channels ?? (isset($attributes['channel']) ? [$attributes['channel']] : ['in_app']),
            'channel' => $attributes['channel'] ?? null,
        ]);
    }

    protected function storeAttachments(CommunicationMessage $message, array $attachments, ?int $uploadedBy = null): void
    {
        foreach ($attachments as $attachment) {
            MessageAttachment::query()->create([
                'school_id' => $message->school_id,
                'message_id' => $message->id,
                'file_name' => $attachment['file_name'],
                'file_path' => $attachment['file_path'],
                'mime_type' => $attachment['mime_type'] ?? null,
                'file_size' => $attachment['file_size'] ?? null,
                'uploaded_by' => $uploadedBy,
            ]);
        }
    }

    protected function baseMessagePayload(array $attributes): array
    {
        return [
            'school_id' => $attributes['school_id'],
            'conversation_id' => $attributes['conversation_id'] ?? null,
            'sender_type' => $attributes['sender_type'] ?? 'user',
            'sender_id' => $attributes['sender_id'] ?? $attributes['sender_user_id'] ?? null,
            'recipient_type' => $attributes['recipient_type'],
            'recipient_id' => $attributes['recipient_id'],
            'subject' => $attributes['subject'] ?? null,
            'body' => $attributes['body'],
            'message_type' => $attributes['message_type'] ?? 'direct',
            'priority' => $attributes['priority'] ?? 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
