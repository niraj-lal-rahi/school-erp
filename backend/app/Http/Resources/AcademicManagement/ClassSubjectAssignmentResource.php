<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassSubjectAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'is_optional' => (bool) $this->is_optional,
            'weekly_periods' => $this->weekly_periods,
            'status' => $this->status,
            'academic_year' => $this->whenLoaded('academicYear', fn () => ['id' => $this->academicYear?->id, 'name' => $this->academicYear?->name]),
            'school_class' => $this->whenLoaded('schoolClass', fn () => ['id' => $this->schoolClass?->id, 'name' => $this->schoolClass?->name]),
            'section' => $this->whenLoaded('section', fn () => $this->section ? ['id' => $this->section->id, 'name' => $this->section->name] : null),
            'subject' => $this->whenLoaded('subject', fn () => ['id' => $this->subject?->id, 'name' => $this->subject?->name, 'code' => $this->subject?->code]),
        ];
    }
}
