<?php

namespace App\Http\Requests\Workflows;

use Closure;
use Illuminate\Validation\Rule;

class StoreAutomationRuleRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueInTenant('automation_rules', 'code')],
            'module' => ['required', 'string', Rule::in($this->moduleOptionsForAutomation())],
            'trigger_type' => ['required', 'string', Rule::in(['event', 'schedule', 'condition'])],
            'trigger_event' => ['nullable', 'string', 'max:255'],
            'schedule_expression' => ['nullable', 'string', 'max:255', $this->validScheduleExpression()],
            'conditions' => ['nullable', 'array', $this->validConditions()],
            'actions' => ['required', 'array', 'min:1', $this->validActions()],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'draft'])],
        ];
    }

    protected function validScheduleExpression(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_string($value) || ! preg_match('/^(\S+\s+){4}\S+$/', trim($value))) {
                $fail('The schedule expression must be a valid five-part cron expression.');
            }
        };
    }

    protected function validConditions(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null) {
                return;
            }

            if (! is_array($value)) {
                $fail('The conditions field must be a valid object payload.');
                return;
            }

            $allowed = ['all', 'any', 'filters', 'operator', 'field', 'value', 'days', 'status'];
            $invalidKeys = array_diff(array_keys($value), $allowed);

            if ($invalidKeys !== []) {
                $fail('The conditions payload contains unsupported keys: '.implode(', ', $invalidKeys).'.');
            }
        };
    }

    protected function validActions(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                $fail('The actions field must be a valid array payload.');
                return;
            }

            foreach ($value as $index => $action) {
                if (! is_array($action)) {
                    $fail("Action at index {$index} must be a valid object payload.");
                    continue;
                }

                if (! isset($action['type']) || ! in_array($action['type'], ['email', 'sms', 'push', 'in_app', 'status_update', 'webhook', 'task', 'reminder'], true)) {
                    $fail("Action at index {$index} must contain a supported type.");
                }
            }
        };
    }
}
