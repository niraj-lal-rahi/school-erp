<?php

namespace App\Http\Resources\AcademicManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicCalendarEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'title' => $this->title,
            'description' => $this->description,
            'event_type' => $this->event_type,
            'start_datetime' => $this->start_datetime?->toDateTimeString(),
            'end_datetime' => $this->end_datetime?->toDateTimeString(),
            'is_holiday' => (bool) $this->is_holiday,
            'audience_type' => $this->audience_type,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'status' => $this->status,
        ];
    }
}
