<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_type' => $this->conversation_type,
            'title' => $this->title,
            'status' => $this->status,
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'participants_count' => $this->whenCounted('participants'),
            'messages_count' => $this->whenCounted('messages'),
            'participants' => ConversationParticipantResource::collection($this->whenLoaded('participants')),
            'messages' => CommunicationMessageResource::collection($this->whenLoaded('messages')),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
