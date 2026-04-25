<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'academic_year_id' => $this->academic_year_id,
            'total_days' => $this->total_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'leave_days' => $this->leave_days,
            'late_days' => $this->late_days,
            'percentage' => (float) $this->percentage,
            'last_updated_at' => $this->last_updated_at?->toISOString(),
            'academic_year' => $this->whenLoaded('academicYear', fn () => [
                'id' => $this->academicYear?->id,
                'name' => $this->academicYear?->name,
                'code' => $this->academicYear?->code,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
