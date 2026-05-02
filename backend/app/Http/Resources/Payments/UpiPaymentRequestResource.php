<?php

namespace App\Http\Resources\Payments;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpiPaymentRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'transaction_id' => $this->transaction_id,
            'upi_vpa' => $this->upi_vpa,
            'payee_name' => $this->payee_name,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'qr_payload' => $this->qr_payload,
            'qr_image_path' => $this->qr_image_path,
            'expires_at' => optional($this->expires_at)?->toAtomString(),
            'status' => $this->status,
            'transaction' => $this->whenLoaded('transaction', fn () => $this->transaction ? [
                'id' => $this->transaction->id,
                'transaction_no' => $this->transaction->transaction_no,
                'provider' => $this->transaction->provider,
                'payment_method' => $this->transaction->payment_method,
                'status' => $this->transaction->status,
                'verification_status' => $this->transaction->verification_status,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
