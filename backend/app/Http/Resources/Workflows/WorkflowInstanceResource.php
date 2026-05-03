<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowInstanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'workflow_definition_id' => $this->workflow_definition_id,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'current_step_id' => $this->current_step_id,
            'status' => $this->status,
            'started_by' => $this->started_by,
            'started_at' => optional($this->started_at)?->toAtomString(),
            'completed_at' => optional($this->completed_at)?->toAtomString(),
            'metadata' => $this->metadata,
            'workflow_definition' => $this->whenLoaded('workflowDefinition', fn () => $this->workflowDefinition ? new WorkflowDefinitionResource($this->workflowDefinition) : null),
            'current_step' => $this->whenLoaded('currentStep', fn () => $this->currentStep ? new WorkflowStepResource($this->currentStep) : null),
            'starter' => $this->whenLoaded('starter', fn () => $this->starter ? [
                'id' => $this->starter->id,
                'name' => $this->starter->name,
                'email' => $this->starter->email,
            ] : null),
            'step_instances' => WorkflowStepInstanceResource::collection($this->whenLoaded('stepInstances')),
            'approval_requests' => ApprovalRequestResource::collection($this->whenLoaded('approvalRequests')),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
