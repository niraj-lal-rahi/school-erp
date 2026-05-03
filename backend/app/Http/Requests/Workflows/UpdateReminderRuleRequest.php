<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateReminderRuleRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', $this->uniqueInTenant('reminder_rules', 'code', $this->routeModelId('id'))],
            'module' => ['sometimes', 'string', Rule::in($this->moduleOptionsForReminder())],
            'reminder_type' => ['sometimes', 'string', Rule::in(['before_due', 'after_due', 'recurring', 'one_time'])],
            'offset_days' => ['nullable', 'integer'],
            'frequency' => ['sometimes', 'string', Rule::in(['once', 'daily', 'weekly', 'monthly'])],
            'channel' => ['sometimes', 'string', Rule::in(['email', 'sms', 'push', 'in_app', 'multi'])],
            'template_id' => ['nullable', 'integer', $this->templateExists()],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $reminderType = $this->input('reminder_type');

                if ($reminderType !== null && in_array($reminderType, ['before_due', 'after_due'], true) && ! $this->filled('offset_days')) {
                    $validator->errors()->add('offset_days', 'Offset days is required for before-due and after-due reminders.');
                }
            },
        ];
    }
}
