<?php

namespace App\Http\Requests\Settings;

class UpdateSecuritySettingRequest extends SettingRequest
{
    public function rules(): array
    {
        return [
            'password_min_length' => ['sometimes', 'integer', 'min:6', 'max:64'],
            'password_requires_uppercase' => ['sometimes', 'boolean'],
            'password_requires_number' => ['sometimes', 'boolean'],
            'password_requires_symbol' => ['sometimes', 'boolean'],
            'session_timeout_minutes' => ['sometimes', 'integer', 'min:5', 'max:1440'],
            'max_login_attempts' => ['sometimes', 'integer', 'min:1', 'max:20'],
            'lockout_minutes' => ['sometimes', 'integer', 'min:1', 'max:1440'],
            'two_factor_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
