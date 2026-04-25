<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'code' => $this->academicYear?->code,
            ]),
            'school_class_id' => $this->school_class_id,
            'school_class' => $this->whenLoaded('schoolClass', fn () => [
                'id' => $this->schoolClass?->id,
                'name' => $this->schoolClass?->name,
                'code' => $this->schoolClass?->code,
            ]),
            'section_id' => $this->section_id,
            'section' => $this->whenLoaded('section', fn () => [
                'id' => $this->section?->id,
                'name' => $this->section?->name,
                'code' => $this->section?->code,
            ]),
            'attendance_date' => $this->attendance_date?->toDateString(),
            'session_type' => $this->session_type,
            'attendance_period_id' => $this->attendance_period_id,
            'period' => $this->whenLoaded('period', fn () => [
                'id' => $this->period?->id,
                'name' => $this->period?->name,
                'code' => $this->period?->code,
                'start_time' => $this->period?->start_time,
                'end_time' => $this->period?->end_time,
            ]),
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn () => [
                'id' => $this->subject?->id,
                'name' => $this->subject?->name,
                'code' => $this->subject?->code,
            ]),
            'teacher_id' => $this->teacher_id,
            'teacher' => $this->whenLoaded('teacher', fn () => [
                'id' => $this->teacher?->id,
                'employee_code' => $this->teacher?->employee_code,
                'full_name' => $this->teacher?->full_name,
            ]),
            'status' => $this->status,
            'marked_by' => $this->marked_by,
            'records_count' => $this->when(isset($this->records_count), $this->records_count),
            'submitted_at' => $this->submitted_at?->toISOString(),
            'locked_at' => $this->locked_at?->toISOString(),
            'records' => StudentAttendanceRecordResource::collection($this->whenLoaded('records')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
