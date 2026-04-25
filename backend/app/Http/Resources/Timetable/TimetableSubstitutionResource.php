<?php

namespace App\Http\Resources\Timetable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetableSubstitutionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timetable_entry_id' => $this->timetable_entry_id,
            'substitution_date' => $this->substitution_date?->toDateString(),
            'reason' => $this->reason,
            'status' => $this->status,
            'original_staff_id' => $this->original_staff_id,
            'substitute_staff_id' => $this->substitute_staff_id,
            'approved_by' => $this->approved_by,
            'timetable_entry' => $this->whenLoaded('timetableEntry', fn () => [
                'id' => $this->timetableEntry?->id,
                'day_of_week' => $this->timetableEntry?->day_of_week,
                'entry_type' => $this->timetableEntry?->entry_type,
                'period' => $this->timetableEntry?->period ? [
                    'id' => $this->timetableEntry->period->id,
                    'name' => $this->timetableEntry->period->name,
                    'code' => $this->timetableEntry->period->code,
                    'start_time' => $this->timetableEntry->period->start_time,
                    'end_time' => $this->timetableEntry->period->end_time,
                ] : null,
                'subject' => $this->timetableEntry?->subject ? [
                    'id' => $this->timetableEntry->subject->id,
                    'name' => $this->timetableEntry->subject->name,
                    'code' => $this->timetableEntry->subject->code,
                ] : null,
                'school_class' => $this->timetableEntry?->schoolClass ? [
                    'id' => $this->timetableEntry->schoolClass->id,
                    'name' => $this->timetableEntry->schoolClass->name,
                    'code' => $this->timetableEntry->schoolClass->code,
                ] : null,
                'section' => $this->timetableEntry?->section ? [
                    'id' => $this->timetableEntry->section->id,
                    'name' => $this->timetableEntry->section->name,
                    'code' => $this->timetableEntry->section->code,
                ] : null,
                'room' => $this->timetableEntry?->room ? [
                    'id' => $this->timetableEntry->room->id,
                    'name' => $this->timetableEntry->room->name,
                    'code' => $this->timetableEntry->room->code,
                ] : null,
                'timetable_version' => $this->timetableEntry?->timetableVersion ? [
                    'id' => $this->timetableEntry->timetableVersion->id,
                    'name' => $this->timetableEntry->timetableVersion->name,
                    'status' => $this->timetableEntry->timetableVersion->status,
                ] : null,
            ]),
            'original_staff' => $this->whenLoaded('originalStaff', fn () => $this->originalStaff ? [
                'id' => $this->originalStaff->id,
                'full_name' => $this->originalStaff->full_name,
                'employee_code' => $this->originalStaff->employee_code,
            ] : null),
            'substitute_staff' => $this->whenLoaded('substituteStaff', fn () => $this->substituteStaff ? [
                'id' => $this->substituteStaff->id,
                'full_name' => $this->substituteStaff->full_name,
                'employee_code' => $this->substituteStaff->employee_code,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
