<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'academic_term_id' => $this->academic_term_id,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'subject_id' => $this->subject_id,
            'staff_id' => $this->staff_id,
            'title' => $this->title,
            'topic' => $this->topic,
            'objectives' => $this->objectives,
            'teaching_method' => $this->teaching_method,
            'planned_date' => $this->planned_date?->toDateString(),
            'duration_minutes' => $this->duration_minutes,
            'materials_needed' => $this->materials_needed,
            'notes' => $this->notes,
            'status' => $this->status,
            'school_class' => $this->whenLoaded('schoolClass', fn () => ['id' => $this->schoolClass?->id, 'name' => $this->schoolClass?->name]),
            'section' => $this->whenLoaded('section', fn () => $this->section ? ['id' => $this->section->id, 'name' => $this->section->name] : null),
            'subject' => $this->whenLoaded('subject', fn () => ['id' => $this->subject?->id, 'name' => $this->subject?->name]),
            'staff' => $this->whenLoaded('staff', fn () => ['id' => $this->staff?->id, 'name' => $this->staff?->name]),
        ];
    }
}
