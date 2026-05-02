<?php

namespace App\Http\Requests\Rbac;

use Illuminate\Validation\Rule;

class UpdateRoleRequest extends RbacRequest
{
    public function rules(): array
    {
        $roleId = $this->routeModelId('id') ?? $this->routeModelId('role');

        return [
            'name' => ['required', 'string', 'max:120', $this->uniqueRoleName($roleId)],
            'code' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', $this->uniqueRoleCode($roleId)],
            'description' => ['nullable', 'string'],
            'is_default' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->resolveRole('id') ?: $this->resolveRole('role');

            if (! $this->canManageRole($role)) {
                $validator->errors()->add('role', 'You are not allowed to modify this role.');
            }
        });
    }
}
