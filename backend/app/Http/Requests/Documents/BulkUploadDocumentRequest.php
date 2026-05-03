<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class BulkUploadDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'upload_type' => ['required', Rule::in(['student', 'staff', 'general'])],
            'mapping' => ['nullable', 'array'],
            'file' => ['required', 'file', 'max:102400', 'mimes:zip,csv,xls,xlsx'],
        ];
    }
}
