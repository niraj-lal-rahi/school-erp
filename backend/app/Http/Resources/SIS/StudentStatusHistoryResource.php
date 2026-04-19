<?php

namespace App\Http\Resources\SIS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'action_type' => $this->action_type,
            'reason' => $this->reason,
            'effective_date' => optional($this->effective_date)->toDateString(),
            'performed_by' => $this->performed_by,
            'performed_by_name' => $this->performer?->name,
            'created_at' => optional($this->created_at)->toAtomString(),
        ];
    }
}
