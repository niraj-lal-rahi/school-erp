<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateNotificationPreferenceRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'user_type' => ['nullable', 'string', Rule::in(['student', 'guardian', 'staff', 'user'])],
            'user_id' => ['nullable', 'integer'],
            'email_enabled' => ['nullable', 'boolean'],
            'sms_enabled' => ['nullable', 'boolean'],
            'push_enabled' => ['nullable', 'boolean'],
            'in_app_enabled' => ['nullable', 'boolean'],
            'quiet_hours_start' => ['nullable', 'date_format:H:i'],
            'quiet_hours_end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('quiet_hours_start') xor $this->filled('quiet_hours_end')) {
                $validator->errors()->add('quiet_hours_end', 'Both quiet_hours_start and quiet_hours_end must be provided together.');
            }

            $type = $this->input('user_type');
            $id = $this->input('user_id');

            if ($type && $id) {
                $table = match ($type) {
                    'student' => 'students',
                    'guardian' => 'guardians',
                    'staff' => 'staff',
                    'user' => 'users',
                    default => null,
                };

                if ($table !== null) {
                    $exists = \DB::table($table)
                        ->where('school_id', $this->tenantId())
                        ->where('id', $id)
                        ->exists();

                    if (! $exists) {
                        $validator->errors()->add('user_id', 'The selected preference owner is invalid for the current tenant.');
                    }
                }
            }
        });
    }
}
