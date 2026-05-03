<?php

namespace App\Http\Resources\Workflows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkflowStepResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'workflow_definition_id' => $this->workflow_definition_id,
            'step_name' => $this->step_name,
            'step_type' => $this->step_type,
            'sequence' => $this->sequence,
            'config' => $this->config,
            'assigned_role_id' => $this->assigned_role_id,
            'assigned_user_id' => $this->assigned_user_id,
            'status' => $this->status,
            'assigned_role' => $this->whenLoaded('assignedRole', fn () => $this->assignedRole ? [
                'id' => $this->assignedRole->id,
                'name' => $this->assignedRole->name,
                'code' => $this->assignedRole->code,
                'slug' => $this->assignedRole->slug,
            ] : null),
            'assigned_user' => $this->whenLoaded('assignedUser', fn () => $this->assignedUser ? [
                'id' => $this->assignedUser->id,
                'name' => $this->assignedUser->name,
                'email' => $this->assignedUser->email,
            ] : null),
            'created_at' => optional($this->created_at)?->toAtomString(),
            'updated_at' => optional($this->updated_at)?->toAtomString(),
            'deleted_at' => optional($this->deleted_at)?->toAtomString(),
        ];
    }
}
