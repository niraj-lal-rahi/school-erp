<?php

namespace App\Http\Requests\Workflows;

class RejectWorkflowStepRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'remarks' => ['required', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
