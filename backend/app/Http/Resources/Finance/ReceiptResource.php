<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_no' => $this->receipt_no,
            'payment_id' => $this->payment_id,
            'student_id' => $this->student_id,
            'receipt_date' => optional($this->receipt_date)->toDateString(),
            'amount' => $this->amount,
            'receipt_pdf_path' => $this->receipt_pdf_path,
            'download_url' => $this->receipt_pdf_path,
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
            'issued_by' => $this->whenLoaded('issuer', fn () => $this->issuer ? [
                'id' => $this->issuer->id,
                'name' => $this->issuer->name,
                'email' => $this->issuer->email,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
