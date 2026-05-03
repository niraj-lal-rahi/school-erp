<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class StoreSettingRequest extends SettingRequest
{
    public function rules(): array
    {
        return [
            'school_id' => ['nullable', 'integer'],
            'group_id' => ['nullable', 'integer', $this->existsInGlobalOrTenant('setting_groups')],
            'key' => ['required', 'string', 'max:150', $this->globalOrTenantUnique('settings', 'key', 'scope')],
            'value' => ['nullable'],
            'value_type' => ['required', Rule::in($this->settingValueTypes())],
            'scope' => ['required', Rule::in($this->settingScopes())],
            'is_sensitive' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
