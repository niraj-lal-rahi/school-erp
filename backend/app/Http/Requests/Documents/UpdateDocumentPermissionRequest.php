<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UpdateDocumentPermissionRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'permission_type' => ['required', Rule::in(['user', 'role', 'owner'])],
            'permission_id' => ['nullable', 'integer'],
            'can_view' => ['nullable', 'boolean'],
            'can_download' => ['nullable', 'boolean'],
            'can_update' => ['nullable', 'boolean'],
            'can_delete' => ['nullable', 'boolean'],
            'can_verify' => ['nullable', 'boolean'],
        ];
    }
}
