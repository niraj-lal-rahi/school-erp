<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class StoreSettingGroupRequest extends SettingRequest
{
    public function rules(): array
    {
        return [
            'school_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueInTenant('setting_groups', 'code')],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
