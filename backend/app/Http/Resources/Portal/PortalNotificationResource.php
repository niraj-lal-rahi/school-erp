<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'student_id' => $this->student_id,
            'title' => $this->title,
            'message' => $this->message,
            'notification_type' => $this->notification_type,
            'is_read' => (bool) $this->is_read,
            'read_at' => optional($this->read_at)->toAtomString(),
            'student' => $this->whenLoaded('student', fn () => $this->student ? [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
                'admission_no' => $this->student->admission_no,
            ] : null),
            'created_at' => optional($this->created_at)->toAtomString(),
            'updated_at' => optional($this->updated_at)->toAtomString(),
        ];
    }
}
