<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'category_id' => ['nullable', 'integer', $this->existsForSchool('document_categories', 'id', $schoolId)],
            'folder_id' => ['nullable', 'integer', $this->existsForSchool('document_folders', 'id', $schoolId)],
            'owner_type' => ['sometimes', ...$this->ownerRule($schoolId)],
            'owner_id' => ['nullable', 'integer'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_no' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'verification_status' => ['nullable', Rule::in(['pending', 'verified', 'rejected', 'expired'])],
            'status' => ['nullable', Rule::in(['active', 'archived', 'deleted'])],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', $this->existsForSchool('document_tags', 'id', $schoolId)],
        ];
    }
}
