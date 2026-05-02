<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentWebhookEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'provider' => $this->provider,
            'event_type' => $this->event_type,
            'event_id' => $this->event_id,
            'processed' => (bool) $this->processed,
            'processed_at' => optional($this->processed_at)?->toAtomString(),
            'error_message' => $this->error_message,
            'payload' => json_decode($this->payload, true),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
