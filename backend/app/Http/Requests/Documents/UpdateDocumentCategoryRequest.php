<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UpdateDocumentCategoryRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');
        $id = $this->routeModelId('id') ?? $this->routeModelId('category');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:100', $this->uniqueForSchool('document_categories', 'code', $schoolId, $id)],
            'description' => ['nullable', 'string'],
            'applies_to' => ['sometimes', 'required', Rule::in(['student', 'staff', 'tenant', 'finance', 'academic', 'general'])],
            'requires_verification' => ['nullable', 'boolean'],
            'has_expiry' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
