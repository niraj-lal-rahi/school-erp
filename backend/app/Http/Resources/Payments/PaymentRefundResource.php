<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentRefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'transaction_id' => $this->transaction_id,
            'refund_no' => $this->refund_no,
            'gateway_refund_id' => $this->gateway_refund_id,
            'amount' => $this->amount,
            'reason' => $this->reason,
            'status' => $this->status,
            'requested_by' => $this->requested_by,
            'processed_at' => optional($this->processed_at)?->toAtomString(),
            'metadata' => $this->metadata,
            'transaction' => $this->whenLoaded('transaction', fn () => $this->transaction ? [
                'id' => $this->transaction->id,
                'transaction_no' => $this->transaction->transaction_no,
                'status' => $this->transaction->status,
                'provider' => $this->transaction->provider,
                'amount' => $this->transaction->amount,
            ] : null),
            'requester' => $this->whenLoaded('requester', fn () => $this->requester ? [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
