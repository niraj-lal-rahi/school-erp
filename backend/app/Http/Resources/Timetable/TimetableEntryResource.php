<?php

namespace App\Http\Resources\Timetable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetableEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timetable_version_id' => $this->timetable_version_id,
            'academic_year_id' => $this->academic_year_id,
            'school_class_id' => $this->school_class_id,
            'section_id' => $this->section_id,
            'day_of_week' => $this->day_of_week,
            'attendance_period_id' => $this->attendance_period_id,
            'subject_id' => $this->subject_id,
            'staff_id' => $this->staff_id,
            'room_id' => $this->room_id,
            'entry_type' => $this->entry_type,
            'notes' => $this->notes,
            'status' => $this->status,
            'timetable_version' => $this->whenLoaded('timetableVersion', fn () => [
                'id' => $this->timetableVersion?->id,
                'name' => $this->timetableVersion?->name,
                'code' => $this->timetableVersion?->code,
                'status' => $this->timetableVersion?->status,
            ]),
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'code' => $this->academicYear?->code,
            ]),
            'school_class' => $this->whenLoaded('schoolClass', fn () => [
                'id' => $this->schoolClass?->id,
                'name' => $this->schoolClass?->name,
                'code' => $this->schoolClass?->code,
            ]),
            'section' => $this->whenLoaded('section', fn () => [
                'id' => $this->section?->id,
                'name' => $this->section?->name,
                'code' => $this->section?->code,
            ]),
            'period' => $this->whenLoaded('period', fn () => [
                'id' => $this->period?->id,
                'name' => $this->period?->name,
                'code' => $this->period?->code,
                'start_time' => $this->period?->start_time,
                'end_time' => $this->period?->end_time,
                'sequence' => $this->period?->sequence,
            ]),
            'subject' => $this->whenLoaded('subject', fn () => $this->subject ? [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
                'code' => $this->subject->code,
            ] : null),
            'staff' => $this->whenLoaded('staff', fn () => $this->staff ? [
                'id' => $this->staff->id,
                'full_name' => $this->staff->full_name,
                'employee_code' => $this->staff->employee_code,
            ] : null),
            'room' => $this->whenLoaded('room', fn () => $this->room ? [
                'id' => $this->room->id,
                'name' => $this->room->name,
                'code' => $this->room->code,
                'room_type' => $this->room->room_type,
            ] : null),
            'substitutions' => TimetableSubstitutionResource::collection($this->whenLoaded('substitutions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
