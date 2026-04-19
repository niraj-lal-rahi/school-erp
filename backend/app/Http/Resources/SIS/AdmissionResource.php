<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'application_no' => $this->application_no,
            'student_id' => $this->student_id,
            'academic_year_id' => $this->academic_year_id,
            'class_id' => $this->applied_class_id,
            'section_id' => $this->section_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => trim(implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]))),
            'gender' => $this->gender,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'guardian_name' => $this->guardian_name,
            'guardian_phone' => $this->guardian_phone,
            'guardian_email' => $this->guardian_email,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'previous_school' => $this->previous_school,
            'remarks' => $this->remarks,
            'application_status' => $this->application_status,
            'submitted_at' => optional($this->submitted_at)->toAtomString(),
            'reviewed_at' => optional($this->reviewed_at)->toAtomString(),
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ]),
            'class' => $this->whenLoaded('appliedClass', fn () => [
                'id' => $this->appliedClass?->id,
                'name' => $this->appliedClass?->name,
                'code' => $this->appliedClass?->code,
            ]),
            'section' => $this->whenLoaded('section', fn () => $this->section ? [
                'id' => $this->section->id,
                'name' => $this->section->name,
            ] : null),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'admission_no' => $this->student->admission_no,
                'full_name' => $this->student->full_name,
            ] : null),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ] : null),
        ];
    }
}
