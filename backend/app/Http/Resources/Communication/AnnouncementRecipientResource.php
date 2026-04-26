<?php

namespace App\Http\Resources\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementRecipientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $recipient = $this->resolveRecipient();

        return [
            'id' => $this->id,
            'recipient_type' => $this->recipient_type,
            'recipient_id' => $this->recipient_id,
            'recipient' => $recipient ? [
                'id' => $recipient->id,
                'name' => $recipient->full_name ?? $recipient->name ?? trim(($recipient->first_name ?? '').' '.($recipient->last_name ?? '')),
                'email' => $recipient->email ?? null,
                'phone' => $recipient->phone ?? null,
            ] : null,
            'read_at' => optional($this->read_at)->toAtomString(),
            'acknowledged_at' => optional($this->acknowledged_at)->toAtomString(),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
