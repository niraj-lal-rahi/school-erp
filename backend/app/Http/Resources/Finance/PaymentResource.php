<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_no' => $this->payment_no,
            'student_id' => $this->student_id,
            'fee_invoice_id' => $this->fee_invoice_id,
            'payment_date' => optional($this->payment_date)->toDateString(),
            'payment_method' => $this->payment_method,
            'gateway_provider' => $this->gateway_provider,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'reference_no' => $this->reference_no,
            'amount' => $this->amount,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
            ] : null),
            'invoice' => $this->whenLoaded('invoice', fn () => $this->invoice ? [
                'id' => $this->invoice->id,
                'invoice_no' => $this->invoice->invoice_no,
                'status' => $this->invoice->status,
                'balance_amount' => $this->invoice->balance_amount,
            ] : null),
            'receipt' => $this->whenLoaded('receipt', fn () => $this->receipt ? [
                'id' => $this->receipt->id,
                'receipt_no' => $this->receipt->receipt_no,
                'receipt_date' => optional($this->receipt->receipt_date)->toDateString(),
            ] : null),
            'allocations' => $this->whenLoaded('allocations', fn () => $this->allocations->map(fn ($allocation) => [
                'id' => $allocation->id,
                'fee_invoice_id' => $allocation->fee_invoice_id,
                'fee_invoice_item_id' => $allocation->fee_invoice_item_id,
                'fee_installment_id' => $allocation->fee_installment_id,
                'allocated_amount' => $allocation->allocated_amount,
            ])->values()),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
