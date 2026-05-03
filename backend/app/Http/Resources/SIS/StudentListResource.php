<?php

namespace App\Http\Resources\SIS;

use App\Http\Resources\Concerns\SupportsIncludes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentListResource extends JsonResource
{
    use SupportsIncludes;

    public function toArray(Request $request): array
    {
        return $this->applySparseFieldset($request, [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'admission_no' => $this->admission_no,
            'roll_no' => $this->roll_no,
            'full_name' => $this->full_name,
            'preferred_name' => $this->preferred_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'current_status' => $this->current_status,
            'admission_date' => optional($this->admission_date)->toDateString(),
            'category' => $this->includeRequested($request, 'category') && $this->relationLoaded('category')
                ? [
                    'id' => $this->category?->id,
                    'name' => $this->category?->name,
                    'code' => $this->category?->code,
                ]
                : null,
            'house' => $this->includeRequested($request, 'house') && $this->relationLoaded('house')
                ? [
                    'id' => $this->house?->id,
                    'name' => $this->house?->name,
                    'code' => $this->house?->code,
                ]
                : null,
            'guardians' => $this->includeRequested($request, 'guardians') && $this->relationLoaded('guardians')
                ? $this->guardians->map(fn ($guardian) => [
                    'id' => $guardian->id,
                    'full_name' => $guardian->full_name,
                    'phone' => $guardian->phone,
                ])->values()
                : null,
            'enrollments' => $this->includeRequested($request, 'enrollments') && $this->relationLoaded('enrollments')
                ? $this->enrollments->map(fn ($enrollment) => [
                    'id' => $enrollment->id,
                    'academic_year_id' => $enrollment->academic_year_id,
                    'school_class_id' => $enrollment->school_class_id,
                    'section_id' => $enrollment->section_id,
                    'is_current' => $enrollment->is_current,
                    'school_class' => $enrollment->relationLoaded('schoolClass') && $enrollment->schoolClass
                        ? ['id' => $enrollment->schoolClass->id, 'name' => $enrollment->schoolClass->name, 'code' => $enrollment->schoolClass->code]
                        : null,
                    'section' => $enrollment->relationLoaded('section') && $enrollment->section
                        ? ['id' => $enrollment->section->id, 'name' => $enrollment->section->name]
                        : null,
                ])->values()
                : null,
            'created_at' => optional($this->created_at)->toAtomString(),
        ]);
    }
}
