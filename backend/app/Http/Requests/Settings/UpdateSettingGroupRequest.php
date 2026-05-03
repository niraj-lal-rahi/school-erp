<?php

namespace App\Http\Requests\Settings;

use Illuminate\Validation\Rule;

class UpdateSettingGroupRequest extends SettingRequest
{
    public function rules(): array
    {
        $groupId = $this->routeModelId('id') ?? $this->routeModelId('group');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:100', $this->uniqueInTenant('setting_groups', 'code', $groupId)],
            'description' => ['nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
