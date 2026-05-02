<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'provider' => $this->provider,
            'mode' => $this->mode,
            'config' => $this->config,
            'supports_upi' => (bool) $this->supports_upi,
            'supports_card' => (bool) $this->supports_card,
            'supports_netbanking' => (bool) $this->supports_netbanking,
            'supports_wallet' => (bool) $this->supports_wallet,
            'status' => $this->status,
            'credentials' => collect($this->whenLoaded('credentials'))->map(fn ($credential) => [
                'id' => $credential->id,
                'key_name' => $credential->key_name,
                'has_value' => filled($credential->getRawOriginal('key_value')),
                'is_encrypted' => (bool) $credential->is_encrypted,
            ])->values(),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
