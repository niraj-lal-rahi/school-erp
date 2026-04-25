<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAttendanceImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file_path' => ['nullable', 'string', 'max:2048'],
            'import_type' => ['required', 'string', Rule::in(['student', 'staff'])],
            'import_date' => ['required', 'date'],
            'rows' => ['nullable', 'array'],
            'rows.*' => ['array'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('file_path') && ! $this->filled('rows')) {
                $validator->errors()->add('rows', 'Either file_path or rows must be provided.');
            }
        });
    }
}
