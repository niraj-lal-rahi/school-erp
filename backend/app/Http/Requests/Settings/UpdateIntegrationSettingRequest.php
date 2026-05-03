<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateIntegrationSettingRequest extends SettingRequest
{
    protected function integrationTypes(): array
    {
        return ['email', 'sms', 'payment', 'storage', 'push', 'maps', 'other'];
    }

    public function rules(): array
    {
        return [
            'school_id' => ['nullable', 'integer'],
            'integration_type' => ['sometimes', Rule::in($this->integrationTypes())],
            'provider' => ['nullable', 'string', 'max:100'],
            'config' => ['nullable', 'array'],
            'encrypted_config' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
