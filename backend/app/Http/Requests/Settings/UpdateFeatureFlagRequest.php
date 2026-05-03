<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateFeatureFlagRequest extends SettingRequest
{
    public function rules(): array
    {
        return [
            'feature_code' => ['sometimes', 'string', 'max:150'],
            'module' => ['sometimes', 'string', 'max:100'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_enabled' => ['sometimes', 'boolean'],
            'rollout_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'config' => ['nullable', 'array'],
        ];
    }
}
