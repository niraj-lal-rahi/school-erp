<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Validation\Rule;

class UpdateWorkflowDefinitionRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', $this->uniqueInTenant('workflow_definitions', 'code', $this->routeModelId('id'))],
            'module' => ['sometimes', 'string', Rule::in($this->moduleOptionsForWorkflow())],
            'description' => ['nullable', 'string'],
            'trigger_type' => ['sometimes', 'string', Rule::in(['event', 'schedule', 'manual'])],
            'trigger_event' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'draft'])],
        ];
    }
}
