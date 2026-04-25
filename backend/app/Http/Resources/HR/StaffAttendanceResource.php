<?php

namespace App\Http\Resources\HR;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'staff_id' => $this->staff_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'attendance_status' => $this->attendance_status,
            'attendance_status_type_id' => $this->attendance_status_type_id,
            'attendance_status_type' => $this->whenLoaded('attendanceStatusType', fn () => $this->attendanceStatusType ? [
                'id' => $this->attendanceStatusType->id,
                'name' => $this->attendanceStatusType->name,
                'code' => $this->attendanceStatusType->code,
                'color_code' => $this->attendanceStatusType->color_code,
                'is_present' => (bool) $this->attendanceStatusType->is_present,
            ] : null),
            'source' => $this->source,
            'remarks' => $this->remarks,
            'staff' => $this->whenLoaded('staff', fn () => [
                'id' => $this->staff?->id,
                'employee_code' => $this->staff?->employee_code,
                'full_name' => $this->staff?->full_name,
            ]),
            'marked_by' => $this->whenLoaded('marker', fn () => $this->marker ? [
                'id' => $this->marker->id,
                'name' => $this->marker->name,
            ] : null),
            'created_at' => $this->created_at?->toAtomString(),
            'updated_at' => $this->updated_at?->toAtomString(),
        ];
    }
}
