<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class StoreDocumentCategoryRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueForSchool('document_categories', 'code', $schoolId)],
            'description' => ['nullable', 'string'],
            'applies_to' => ['required', Rule::in(['student', 'staff', 'tenant', 'finance', 'academic', 'general'])],
            'requires_verification' => ['nullable', 'boolean'],
            'has_expiry' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }
}
