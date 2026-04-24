<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'issue_date' => optional($this->issue_date)->toDateString(),
            'due_date' => optional($this->due_date)->toDateString(),
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'fine_total' => $this->fine_total,
            'tax_total' => $this->tax_total,
            'grand_total' => $this->grand_total,
            'paid_amount' => $this->paid_amount,
            'balance_amount' => $this->balance_amount,
            'status' => $this->status,
            'notes' => $this->notes,
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
            ] : null),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'fee_installment_id' => $item->fee_installment_id,
                'fee_head_id' => $item->fee_head_id,
                'description' => $item->description,
                'amount' => $item->amount,
                'discount_amount' => $item->discount_amount,
                'fine_amount' => $item->fine_amount,
                'total_amount' => $item->total_amount,
                'fee_head' => $item->feeHead ? [
                    'id' => $item->feeHead->id,
                    'name' => $item->feeHead->name,
                    'code' => $item->feeHead->code,
                ] : null,
                'installment' => $item->installment ? [
                    'id' => $item->installment->id,
                    'installment_name' => $item->installment->installment_name,
                    'due_date' => optional($item->installment->due_date)->toDateString(),
                ] : null,
            ])->values()),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
