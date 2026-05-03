<?php

namespace App\Http\Requests\Workflows;

class ApproveWorkflowStepRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'remarks' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
