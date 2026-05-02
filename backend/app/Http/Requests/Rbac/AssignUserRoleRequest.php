<?php

namespace App\Http\Requests\Rbac;

use App\Models\Role;

class AssignUserRoleRequest extends RbacRequest
{
    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $role = Role::withoutGlobalScopes()->find($this->integer('role_id'));

            if (! $this->canManageRole($role)) {
                $validator->errors()->add('role_id', 'You are not allowed to assign the selected role.');
                return;
            }

            $userId = $this->routeModelId('id') ?? $this->routeModelId('user');

            if (! $userId) {
                $validator->errors()->add('user', 'A valid user is required for role assignment.');
            }
        });
    }
}
