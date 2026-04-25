<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => $this->whenLoaded('student', fn () => [
                'id' => $this->student?->id,
                'admission_no' => $this->student?->admission_no,
                'full_name' => $this->student?->full_name,
            ]),
            'attendance_status_type_id' => $this->attendance_status_type_id,
            'attendance_status' => $this->whenLoaded('attendanceStatus', fn () => [
                'id' => $this->attendanceStatus?->id,
                'name' => $this->attendanceStatus?->name,
                'code' => $this->attendanceStatus?->code,
                'color_code' => $this->attendanceStatus?->color_code,
                'is_present' => (bool) $this->attendanceStatus?->is_present,
            ]),
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'remarks' => $this->remarks,
            'marked_by' => $this->marked_by,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
