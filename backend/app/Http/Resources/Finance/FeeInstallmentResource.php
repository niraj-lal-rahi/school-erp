<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeInstallmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_fee_assignment_id' => $this->student_fee_assignment_id,
            'fee_head_id' => $this->fee_head_id,
            'installment_name' => $this->installment_name,
            'due_date' => optional($this->due_date)->toDateString(),
            'amount' => $this->amount,
            'discount_amount' => $this->discount_amount,
            'fine_amount' => $this->fine_amount,
            'paid_amount' => $this->paid_amount,
            'balance_amount' => $this->balance_amount,
            'status' => $this->status,
            'fee_head' => $this->whenLoaded('feeHead', fn () => $this->feeHead ? [
                'id' => $this->feeHead->id,
                'name' => $this->feeHead->name,
                'code' => $this->feeHead->code,
            ] : null),
            'student_fee_assignment' => $this->whenLoaded('studentFeeAssignment', fn () => $this->studentFeeAssignment ? [
                'id' => $this->studentFeeAssignment->id,
                'status' => $this->studentFeeAssignment->status,
                'student' => $this->studentFeeAssignment->student ? [
                    'id' => $this->studentFeeAssignment->student->id,
                    'full_name' => $this->studentFeeAssignment->student->full_name,
                    'admission_no' => $this->studentFeeAssignment->student->admission_no,
                ] : null,
                'academic_year' => $this->studentFeeAssignment->academicYear ? [
                    'id' => $this->studentFeeAssignment->academicYear->id,
                    'name' => $this->studentFeeAssignment->academicYear->name,
                    'code' => $this->studentFeeAssignment->academicYear->code,
                ] : null,
                'school_class' => $this->studentFeeAssignment->schoolClass ? [
                    'id' => $this->studentFeeAssignment->schoolClass->id,
                    'name' => $this->studentFeeAssignment->schoolClass->name,
                    'code' => $this->studentFeeAssignment->schoolClass->code,
                ] : null,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
