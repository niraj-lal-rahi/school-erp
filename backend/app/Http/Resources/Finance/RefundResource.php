<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'refund_no' => $this->refund_no,
            'payment_id' => $this->payment_id,
            'student_id' => $this->student_id,
            'refund_date' => optional($this->refund_date)->toDateString(),
            'amount' => $this->amount,
            'reason' => $this->reason,
            'status' => $this->status,
            'payment' => $this->whenLoaded('payment', fn () => $this->payment ? [
                'id' => $this->payment->id,
                'payment_no' => $this->payment->payment_no,
                'status' => $this->payment->status,
                'amount' => $this->payment->amount,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ] : null),
            'processor' => $this->whenLoaded('processor', fn () => $this->processor ? [
                'id' => $this->processor->id,
                'name' => $this->processor->name,
                'email' => $this->processor->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
