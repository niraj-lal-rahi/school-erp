<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDiscountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'discount_type_id' => $this->discount_type_id,
            'fee_head_id' => $this->fee_head_id,
            'discount_amount' => $this->discount_amount,
            'reason' => $this->reason,
            'approved_at' => optional($this->approved_at)->toAtomString(),
            'status' => $this->status,
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
            'discount_type_item' => $this->whenLoaded('discountType', fn () => $this->discountType ? [
                'id' => $this->discountType->id,
                'name' => $this->discountType->name,
                'code' => $this->discountType->code,
                'discount_type' => $this->discountType->discount_type,
            ] : null),
            'fee_head' => $this->whenLoaded('feeHead', fn () => $this->feeHead ? [
                'id' => $this->feeHead->id,
                'name' => $this->feeHead->name,
                'code' => $this->feeHead->code,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
