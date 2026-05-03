<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'module' => $this->module,
            'description' => $this->description,
            'trigger_type' => $this->trigger_type,
            'trigger_event' => $this->trigger_event,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'steps_count' => $this->whenCounted('steps'),
            'instances_count' => $this->whenCounted('instances'),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ] : null),
            'steps' => WorkflowStepResource::collection($this->whenLoaded('steps')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
