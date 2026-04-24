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
            'attendance_date' => optional($this->attendance_date)->toDateString(),
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'attendance_status' => $this->attendance_status,
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
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
