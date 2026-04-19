<?php

namespace App\Http\Requests\SIS;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class UploadStudentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Student $student */
        $student = $this->route('student');

        return $this->user()?->can('uploadDocument', $student) ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
