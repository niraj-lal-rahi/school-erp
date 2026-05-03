<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UpdateDocumentFolderRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');
        $id = $this->routeModelId('id') ?? $this->routeModelId('folder');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'parent_id' => ['nullable', 'integer', 'different:id', $this->existsForSchool('document_folders', 'id', $schoolId)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', $this->uniqueForSchool('document_folders', 'code', $schoolId, $id)],
            'description' => ['nullable', 'string'],
            'visibility' => ['nullable', Rule::in(['private', 'internal', 'shared'])],
        ];
    }
}
