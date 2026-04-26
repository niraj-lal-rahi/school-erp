<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sender = $this->resolveSender();
        $recipient = $this->resolveRecipient();

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_type' => $this->sender_type,
            'sender_id' => $this->sender_id,
            'recipient_type' => $this->recipient_type,
            'recipient_id' => $this->recipient_id,
            'subject' => $this->subject,
            'body' => $this->body,
            'message_type' => $this->message_type,
            'priority' => $this->priority,
            'status' => $this->status,
            'sent_at' => optional($this->sent_at)->toAtomString(),
            'read_at' => optional($this->read_at)->toAtomString(),
            'sender' => $sender ? [
                'id' => $sender->id,
                'name' => $sender->full_name ?? $sender->name ?? trim(($sender->first_name ?? '').' '.($sender->last_name ?? '')),
                'email' => $sender->email ?? null,
            ] : null,
            'recipient' => $recipient ? [
                'id' => $recipient->id,
                'name' => $recipient->full_name ?? $recipient->name ?? trim(($recipient->first_name ?? '').' '.($recipient->last_name ?? '')),
                'email' => $recipient->email ?? null,
            ] : null,
            'conversation' => $this->whenLoaded('conversation', fn () => $this->conversation ? [
                'id' => $this->conversation->id,
                'conversation_type' => $this->conversation->conversation_type,
                'title' => $this->conversation->title,
                'status' => $this->conversation->status,
            ] : null),
            'attachments' => MessageAttachmentResource::collection($this->whenLoaded('attachments')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
