<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'school_class_id' => $this->school_class_id,
            'subject_id' => $this->subject_id,
            'academic_term_id' => $this->academic_term_id,
            'title' => $this->title,
            'description' => $this->description,
            'sequence' => $this->sequence,
            'learning_outcomes' => $this->learning_outcomes,
            'status' => $this->status,
            'school_class' => $this->whenLoaded('schoolClass', fn () => ['id' => $this->schoolClass?->id, 'name' => $this->schoolClass?->name]),
            'subject' => $this->whenLoaded('subject', fn () => ['id' => $this->subject?->id, 'name' => $this->subject?->name]),
            'term' => $this->whenLoaded('term', fn () => $this->term ? ['id' => $this->term->id, 'name' => $this->term->name] : null),
        ];
    }
}
