<?php

namespace App\Http\Requests\SIS;

use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var StudentDocument $studentDocument */
        $studentDocument = $this->route('studentDocument');

        return $this->user()?->can('update', $studentDocument) ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'verification_status' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
