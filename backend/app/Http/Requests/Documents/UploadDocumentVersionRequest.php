<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class UploadDocumentVersionRequest extends DocumentRequest
{
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'disk' => ['nullable', Rule::in(['local', 'public', 's3'])],
            'file' => $this->fileRules(true),
        ];
    }
}
