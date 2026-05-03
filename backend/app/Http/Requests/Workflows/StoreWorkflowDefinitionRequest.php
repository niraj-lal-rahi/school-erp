<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Validation\Rule;

class StoreWorkflowDefinitionRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueInTenant('workflow_definitions', 'code')],
            'module' => ['required', 'string', Rule::in($this->moduleOptionsForWorkflow())],
            'description' => ['nullable', 'string'],
            'trigger_type' => ['required', 'string', Rule::in(['event', 'schedule', 'manual'])],
            'trigger_event' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'draft'])],
        ];
    }
}
