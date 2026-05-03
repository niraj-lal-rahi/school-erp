<?php

namespace App\Http\Requests\Workflows;

class RunAutomationRuleRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'metadata' => ['nullable', 'array'],
            'reference_type' => ['nullable', 'string', 'max:150'],
            'reference_id' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
