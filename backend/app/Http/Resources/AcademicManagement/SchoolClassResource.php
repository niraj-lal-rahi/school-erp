<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'code' => $this->code,
            'grade_level' => $this->grade_level,
            'level_order' => $this->level_order,
            'description' => $this->description,
            'status' => $this->status,
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
            ]),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
        ];
    }
}
