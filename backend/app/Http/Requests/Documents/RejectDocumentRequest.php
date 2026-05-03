<?php

namespace App\Http\Requests\Documents;

use Illuminate\Validation\Rule;

class RejectDocumentRequest extends DocumentRequest
{
    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')],
            'remarks' => ['required', 'string'],
        ];
    }
}
