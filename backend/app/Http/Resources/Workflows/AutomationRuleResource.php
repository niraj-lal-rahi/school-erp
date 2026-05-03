<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'module' => $this->module,
            'trigger_type' => $this->trigger_type,
            'trigger_event' => $this->trigger_event,
            'schedule_expression' => $this->schedule_expression,
            'conditions' => $this->conditions,
            'actions' => $this->actions,
            'status' => $this->status,
            'last_run_at' => optional($this->last_run_at)?->toAtomString(),
            'next_run_at' => optional($this->next_run_at)?->toAtomString(),
            'created_by' => $this->created_by,
            'runs_count' => $this->whenCounted('runs'),
            'action_logs_count' => $this->whenCounted('actionLogs'),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
