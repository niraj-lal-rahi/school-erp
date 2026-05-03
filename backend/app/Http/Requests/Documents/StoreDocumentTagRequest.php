<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class StoreDocumentTagRequest extends DocumentRequest
{
    public function rules(): array
    {
        $schoolId = $this->integer('school_id');

        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', $this->uniqueForSchool('document_tags', 'code', $schoolId)],
        ];
    }
}
