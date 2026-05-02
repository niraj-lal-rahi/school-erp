<?php

namespace App\Http\Requests\Rbac;

class AssignPermissionsRequest extends RbacRequest
{
    public function rules(): array
    {
        return [
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = $this->resolveRole('id') ?: $this->resolveRole('role');

            if (! $this->canManageRole($role)) {
                $validator->errors()->add('role', 'You are not allowed to modify permissions for this role.');
            }
        });
    }
}
