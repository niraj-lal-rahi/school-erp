<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $participant = $this->resolveParticipant();

        return [
            'id' => $this->id,
            'participant_type' => $this->participant_type,
            'participant_id' => $this->participant_id,
            'participant' => $participant ? [
                'id' => $participant->id,
                'name' => $participant->full_name ?? $participant->name ?? trim(($participant->first_name ?? '').' '.($participant->last_name ?? '')),
                'email' => $participant->email ?? null,
                'phone' => $participant->phone ?? null,
            ] : null,
            'joined_at' => optional($this->joined_at)->toAtomString(),
            'left_at' => optional($this->left_at)->toAtomString(),
            'is_muted' => (bool) $this->is_muted,
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
