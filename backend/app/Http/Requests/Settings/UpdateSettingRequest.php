<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateSettingRequest extends SettingRequest
{
    public function rules(): array
    {
        $settingId = $this->routeModelId('id') ?? $this->routeModelId('setting');

        return [
            'group_id' => ['sometimes', 'nullable', 'integer', $this->existsInGlobalOrTenant('setting_groups')],
            'key' => ['sometimes', 'string', 'max:150', $this->globalOrTenantUnique('settings', 'key', 'scope', $this->input('scope'), $settingId)],
            'value' => ['nullable'],
            'value_type' => ['sometimes', Rule::in($this->settingValueTypes())],
            'scope' => ['sometimes', Rule::in($this->settingScopes())],
            'is_sensitive' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
