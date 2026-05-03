<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStepInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'workflow_step_id' => $this->workflow_step_id,
            'assigned_to' => $this->assigned_to,
            'status' => $this->status,
            'action_taken_by' => $this->action_taken_by,
            'action_taken_at' => optional($this->action_taken_at)?->toAtomString(),
            'remarks' => $this->remarks,
            'metadata' => $this->metadata,
            'workflow_step' => $this->whenLoaded('workflowStep', fn () => $this->workflowStep ? new WorkflowStepResource($this->workflowStep) : null),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
                'email' => $this->assignee->email,
            ] : null),
            'actor' => $this->whenLoaded('actor', fn () => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
                'email' => $this->actor->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
        ];
    }
}
