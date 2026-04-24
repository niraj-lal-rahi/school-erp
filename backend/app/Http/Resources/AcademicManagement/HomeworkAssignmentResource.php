<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkAssignmentResource extends JsonResource
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
            'description' => $this->description,
            'assigned_date' => $this->assigned_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'total_marks' => $this->total_marks,
            'attachment_path' => $this->attachment_path,
            'status' => $this->status,
            'subject' => $this->whenLoaded('subject', fn () => ['id' => $this->subject?->id, 'name' => $this->subject?->name]),
            'staff' => $this->whenLoaded('staff', fn () => ['id' => $this->staff?->id, 'name' => $this->staff?->full_name, 'employee_code' => $this->staff?->employee_code]),
        ];
    }
}
