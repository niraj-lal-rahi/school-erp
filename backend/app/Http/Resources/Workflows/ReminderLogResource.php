<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'reminder_rule_id' => $this->reminder_rule_id,
            'recipient_type' => $this->recipient_type,
            'recipient_id' => $this->recipient_id,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'channel' => $this->channel,
            'status' => $this->status,
            'sent_at' => optional($this->sent_at)?->toAtomString(),
            'error_message' => $this->error_message,
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
