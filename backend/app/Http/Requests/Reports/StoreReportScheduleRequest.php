<?php

namespace App\Http\Requests\Reports;

use Closure;
use Illuminate\Validation\Rule;

class StoreReportScheduleRequest extends ReportsRequest
{
    public function rules(): array
    {
        return [
            'report_definition_id' => ['required', 'integer', $this->existsInTenant('report_definitions')],
            'schedule_type' => ['required', 'string', Rule::in(['once', 'daily', 'weekly', 'monthly'])],
            'schedule_config' => ['required', 'array', $this->validScheduleConfig()],
            'schedule_config.time' => ['required', 'date_format:H:i'],
            'schedule_config.run_at' => ['nullable', 'date'],
            'schedule_config.day_of_week' => ['nullable', 'integer', 'between:0,6'],
            'schedule_config.day_of_month' => ['nullable', 'integer', 'between:1,31'],
            'next_run_at' => ['nullable', 'date'],
            'last_run_at' => ['nullable', 'date'],
            'channel' => ['required', 'string', Rule::in(['email', 'in_app'])],
            'recipients' => ['required', 'array', 'min:1', $this->validRecipients()],
            'recipients.*.user_type' => ['nullable', 'string', Rule::in(['student', 'guardian', 'staff', 'user', 'admin'])],
            'recipients.*.user_id' => ['nullable', 'integer'],
            'recipients.*.email' => ['nullable', 'email:rfc,dns'],
            'status' => ['sometimes', 'string', Rule::in(['active', 'paused', 'cancelled'])],
        ];
    }

    protected function validScheduleConfig(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                $fail('The schedule config must be a valid object payload.');
                return;
            }

            $scheduleType = $this->input('schedule_type');

            if ($scheduleType === 'once' && empty($value['run_at'])) {
                $fail('The schedule config run_at field is required for once schedules.');
            }

            if ($scheduleType === 'weekly' && ! array_key_exists('day_of_week', $value)) {
                $fail('The schedule config day_of_week field is required for weekly schedules.');
            }

            if ($scheduleType === 'monthly' && ! array_key_exists('day_of_month', $value)) {
                $fail('The schedule config day_of_month field is required for monthly schedules.');
            }
        };
    }

    protected function validRecipients(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                $fail('The recipients field must be a valid list.');
                return;
            }

            foreach ($value as $recipient) {
                if (! is_array($recipient)) {
                    $fail('Each recipient must be a valid object payload.');
                    return;
                }

                $hasUserReference = ! empty($recipient['user_type']) && ! empty($recipient['user_id']);
                $hasEmail = ! empty($recipient['email']);

                if (! $hasUserReference && ! $hasEmail) {
                    $fail('Each recipient must include either a user_type and user_id pair or an email.');
                    return;
                }
            }
        };
    }
}
