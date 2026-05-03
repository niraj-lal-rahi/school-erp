<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'workflow_instance_id' => $this->workflow_instance_id,
            'module' => $this->module,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'requested_by' => $this->requested_by,
            'approver_id' => $this->approver_id,
            'approver_role_id' => $this->approver_role_id,
            'status' => $this->status,
            'requested_at' => optional($this->requested_at)?->toAtomString(),
            'responded_at' => optional($this->responded_at)?->toAtomString(),
            'remarks' => $this->remarks,
            'workflow_instance' => $this->whenLoaded('workflowInstance', fn () => $this->workflowInstance ? new WorkflowInstanceResource($this->workflowInstance) : null),
            'requester' => $this->whenLoaded('requester', fn () => $this->requester ? [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'email' => $this->requester->email,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
                'email' => $this->approver->email,
            ] : null),
            'approver_role' => $this->whenLoaded('approverRole', fn () => $this->approverRole ? [
                'id' => $this->approverRole->id,
                'name' => $this->approverRole->name,
                'code' => $this->approverRole->code,
                'slug' => $this->approverRole->slug,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
