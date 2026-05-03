<?php

namespace App\Http\Requests\Workflows;

class StartWorkflowRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'workflow_definition_id' => ['required', 'integer', $this->existsInTenant('workflow_definitions')],
            'reference_type' => ['required', 'string', 'max:150'],
            'reference_id' => ['required', 'integer', 'min:1'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
