<?php

namespace App\Http\Requests\Portal;

use Illuminate\Contracts\Validation\Validator;

class UpdatePortalNotificationPreferenceRequest extends PortalRequest
{
    public function rules(): array
    {
        return [
            'user_type' => ['required', 'in:student,guardian,staff,user'],
            'user_id' => ['required', 'integer'],
            'email_enabled' => ['sometimes', 'boolean'],
            'sms_enabled' => ['sometimes', 'boolean'],
            'push_enabled' => ['sometimes', 'boolean'],
            'in_app_enabled' => ['sometimes', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('quiet_hours_start') xor $this->filled('quiet_hours_end')) {
                $validator->errors()->add('quiet_hours_start', 'Both quiet hour start and end times are required together.');
            }

            if ($this->input('user_type') === 'student' && ! $this->userOwnsStudent($this->integer('user_id')) && $this->user()?->id === $this->integer('user_id')) {
                $validator->errors()->add('user_id', 'A student can only update notification preferences for their own profile.');
            }
        });
    }
}
