<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class StoreDocumentFolderRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'parent_id' => ['nullable', 'integer', $this->existsForSchool('document_folders', 'id', $schoolId)],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', $this->uniqueForSchool('document_folders', 'code', $schoolId)],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', Rule::in(['private', 'internal', 'shared'])],
            'created_by' => ['nullable', 'integer', $this->userExists($schoolId)],
        ];
    }
}
