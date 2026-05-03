<?php

namespace App\Http\Resources\Finance;

use App\Http\Resources\Concerns\SupportsIncludes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceListResource extends JsonResource
{
    use SupportsIncludes;

    public function toArray(Request $request): array
    {
        return $this->applySparseFieldset($request, [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'issue_date' => optional($this->issue_date)->toDateString(),
            'due_date' => optional($this->due_date)->toDateString(),
            'grand_total' => $this->grand_total,
            'paid_amount' => $this->paid_amount,
            'balance_amount' => $this->balance_amount,
            'status' => $this->status,
            'student' => $this->includeRequested($request, 'student') && $this->relationLoaded('student')
                ? [
                    'id' => $this->student?->id,
                    'full_name' => $this->student?->full_name,
                    'admission_no' => $this->student?->admission_no,
                ]
                : null,
            'academic_year' => $this->includeRequested($request, 'academicYear') && $this->relationLoaded('academicYear')
                ? [
                    'id' => $this->academicYear?->id,
                    'name' => $this->academicYear?->name,
                    'code' => $this->academicYear?->code,
                ]
                : null,
            'created_by' => $this->includeRequested($request, 'creator') && $this->relationLoaded('creator')
                ? [
                    'id' => $this->creator?->id,
                    'name' => $this->creator?->name,
                ]
                : null,
            'created_at' => optional($this->created_at)->toAtomString(),
        ]);
    }
}
