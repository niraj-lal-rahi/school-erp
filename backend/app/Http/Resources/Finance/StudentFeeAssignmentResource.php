<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'fee_structure_id' => $this->fee_structure_id,
            'assigned_date' => optional($this->assigned_date)->toDateString(),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student?->id,
                'full_name' => $this->student?->full_name,
                'admission_no' => $this->student?->admission_no,
            ]),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
                'code' => $this->academicYear->code,
            ] : null),
            'school_class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
                'code' => $this->schoolClass->code,
            ] : null),
            'section' => $this->whenLoaded('section', fn () => $this->section ? [
                'id' => $this->section->id,
                'name' => $this->section->name,
                'code' => $this->section->code,
            ] : null),
            'fee_structure' => $this->whenLoaded('feeStructure', fn () => $this->feeStructure ? [
                'id' => $this->feeStructure->id,
                'name' => $this->feeStructure->name,
                'code' => $this->feeStructure->code,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
