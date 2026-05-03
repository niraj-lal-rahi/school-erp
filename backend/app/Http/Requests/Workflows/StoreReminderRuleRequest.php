<?php

namespace App\Http\Requests\Workflows;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReminderRuleRequest extends WorkflowRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueInTenant('reminder_rules', 'code')],
            'module' => ['required', 'string', Rule::in($this->moduleOptionsForReminder())],
            'reminder_type' => ['required', 'string', Rule::in(['before_due', 'after_due', 'recurring', 'one_time'])],
            'offset_days' => ['nullable', 'integer'],
            'frequency' => ['required', 'string', Rule::in(['once', 'daily', 'weekly', 'monthly'])],
            'channel' => ['required', 'string', Rule::in(['email', 'sms', 'push', 'in_app', 'multi'])],
            'template_id' => ['nullable', 'integer', $this->templateExists()],
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

                if (in_array($this->input('reminder_type'), ['before_due', 'after_due'], true) && ! $this->filled('offset_days')) {
                    $validator->errors()->add('offset_days', 'Offset days is required for before-due and after-due reminders.');
                }
            },
        ];
    }
}
