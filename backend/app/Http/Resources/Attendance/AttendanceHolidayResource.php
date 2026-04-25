<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceHolidayResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'applies_to' => $this->applies_to,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'is_recurring' => (bool) $this->is_recurring,
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'code' => $this->academicYear?->code,
            ]),
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
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
