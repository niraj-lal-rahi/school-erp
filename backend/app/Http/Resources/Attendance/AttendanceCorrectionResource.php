<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'attendance_date' => $this->attendance_date?->toDateString(),
            'old_status_id' => $this->old_status_id,
            'new_status_id' => $this->new_status_id,
            'old_status' => $this->whenLoaded('oldStatus', fn () => $this->oldStatus ? [
                'id' => $this->oldStatus->id,
                'name' => $this->oldStatus->name,
                'code' => $this->oldStatus->code,
            ] : null),
            'new_status' => $this->whenLoaded('newStatus', fn () => $this->newStatus ? [
                'id' => $this->newStatus->id,
                'name' => $this->newStatus->name,
                'code' => $this->newStatus->code,
            ] : null),
            'reason' => $this->reason,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'review_remarks' => $this->review_remarks,
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
