<?php

namespace App\Http\Requests\Workflows;

use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWorkflowStepRequest extends WorkflowRequest
{
    public function rules(): array
    {
        $workflowDefinitionId = $this->integer('workflow_definition_id') ?: $this->routeModelId('id');

        return [
            'workflow_definition_id' => ['sometimes', 'integer', $this->existsInTenant('workflow_definitions')],
            'step_name' => ['required', 'string', 'max:255'],
            'step_type' => ['required', 'string', Rule::in(['approval', 'notification', 'condition', 'action', 'delay'])],
            'sequence' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('workflow_steps', 'sequence')
                    ->where(fn ($query) => $query->where('workflow_definition_id', $workflowDefinitionId))
                    ->ignore($this->routeModelId('step')),
            ],
            'config' => ['nullable', 'array', $this->validStepConfig()],
            'assigned_role_id' => ['nullable', 'integer', $this->roleExists()],
            'assigned_user_id' => ['nullable', 'integer', $this->userExists()],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('step_type') === 'approval' && ! $this->filled('assigned_role_id') && ! $this->filled('assigned_user_id')) {
                    $validator->errors()->add('assigned_role_id', 'An approval step must have an assigned role or assigned user.');
                }
            },
        ];
    }

    protected function validStepConfig(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null) {
                return;
            }

            if (! is_array($value)) {
                $fail('The step config must be a valid object payload.');
                return;
            }

            $allowedKeys = ['conditions', 'actions', 'delay_minutes', 'delay_until', 'channels', 'template_id', 'webhook_url', 'status_field', 'status_value'];
            $invalidKeys = array_diff(array_keys($value), $allowedKeys);

            if ($invalidKeys !== []) {
                $fail('The step config contains unsupported keys: '.implode(', ', $invalidKeys).'.');
            }

            if (isset($value['template_id']) && ! is_numeric($value['template_id'])) {
                $fail('The step config template_id must be a valid template identifier.');
            }
        };
    }
}
