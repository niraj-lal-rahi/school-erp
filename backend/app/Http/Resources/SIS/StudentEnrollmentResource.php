<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'roll_number' => $this->roll_number,
            'enrollment_date' => optional($this->enrollment_date)->toDateString(),
            'joined_on' => optional($this->joined_on)->toDateString(),
            'ended_on' => optional($this->ended_on)->toDateString(),
            'status' => $this->status,
            'is_current' => (bool) $this->is_current,
            'remarks' => $this->remarks,
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'admission_no' => $this->student->admission_no,
                'full_name' => $this->student->full_name,
            ] : null),
            'academic_year' => $this->whenLoaded('academicYear', fn () => $this->academicYear ? [
                'id' => $this->academicYear->id,
                'name' => $this->academicYear->name,
            ] : null),
            'school_class' => $this->whenLoaded('schoolClass', fn () => $this->schoolClass ? [
                'id' => $this->schoolClass->id,
                'name' => $this->schoolClass->name,
                'code' => $this->schoolClass->code,
            ] : null),
            'section' => $this->whenLoaded('section', fn () => $this->section ? [
                'id' => $this->section->id,
                'name' => $this->section->name,
            ] : null),
        ];
    }
}
