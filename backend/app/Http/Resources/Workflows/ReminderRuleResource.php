<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReminderRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'module' => $this->module,
            'reminder_type' => $this->reminder_type,
            'offset_days' => $this->offset_days,
            'frequency' => $this->frequency,
            'channel' => $this->channel,
            'template_id' => $this->template_id,
            'status' => $this->status,
            'logs_count' => $this->whenCounted('logs'),
            'template' => $this->whenLoaded('template', fn () => $this->template ? [
                'id' => $this->template->id,
                'name' => $this->template->name,
                'code' => $this->template->code,
                'template_type' => $this->template->template_type,
            ] : null),
            'logs' => ReminderLogResource::collection($this->whenLoaded('logs')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
