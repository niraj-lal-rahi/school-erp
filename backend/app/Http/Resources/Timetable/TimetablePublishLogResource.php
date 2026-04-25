<?php

namespace App\Http\Resources\Timetable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimetablePublishLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timetable_version_id' => $this->timetable_version_id,
            'action' => $this->action,
            'remarks' => $this->remarks,
            'performed_by' => $this->performed_by,
            'performer' => $this->whenLoaded('performer', fn () => $this->performer ? [
                'id' => $this->performer->id,
                'name' => $this->performer->name,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
