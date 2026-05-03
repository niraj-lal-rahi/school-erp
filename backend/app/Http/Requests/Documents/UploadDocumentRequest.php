<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UploadDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'category_id' => ['nullable', 'integer', $this->existsForSchool('document_categories', 'id', $schoolId)],
            'folder_id' => ['nullable', 'integer', $this->existsForSchool('document_folders', 'id', $schoolId)],
            'owner_type' => $this->ownerRule($schoolId),
            'owner_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_no' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'verification_status' => ['nullable', Rule::in(['pending', 'verified', 'rejected', 'expired'])],
            'status' => ['nullable', Rule::in(['active', 'archived', 'deleted'])],
            'disk' => ['nullable', Rule::in(['local', 'public', 's3'])],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', $this->existsForSchool('document_tags', 'id', $schoolId)],
            'permissions' => $this->permissionRules($schoolId),
            'permissions.*.permission_type' => ['required_with:permissions', Rule::in(['user', 'role', 'owner'])],
            'permissions.*.permission_id' => ['nullable', 'integer'],
            'permissions.*.can_view' => ['nullable', 'boolean'],
            'permissions.*.can_download' => ['nullable', 'boolean'],
            'permissions.*.can_update' => ['nullable', 'boolean'],
            'permissions.*.can_delete' => ['nullable', 'boolean'],
            'permissions.*.can_verify' => ['nullable', 'boolean'],
            'file' => $this->fileRules(true),
        ];
    }
}
