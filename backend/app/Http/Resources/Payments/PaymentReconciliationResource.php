<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReconciliationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'transaction_id' => $this->transaction_id,
            'source' => $this->source,
            'old_status' => $this->old_status,
            'new_status' => $this->new_status,
            'reconciled_by' => $this->reconciled_by,
            'reconciled_at' => optional($this->reconciled_at)?->toAtomString(),
            'remarks' => $this->remarks,
            'metadata' => $this->metadata,
            'transaction' => $this->whenLoaded('transaction', fn () => $this->transaction ? [
                'id' => $this->transaction->id,
                'transaction_no' => $this->transaction->transaction_no,
                'provider' => $this->transaction->provider,
                'status' => $this->transaction->status,
            ] : null),
            'reconciler' => $this->whenLoaded('reconciler', fn () => $this->reconciler ? [
                'id' => $this->reconciler->id,
                'name' => $this->reconciler->name,
                'email' => $this->reconciler->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
