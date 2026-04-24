<?php

namespace App\Http\Requests\HR;

use App\Models\HR\Staff;
use Illuminate\Foundation\Http\FormRequest;

class UploadStaffDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');

        return $staff instanceof Staff
            ? ($this->user()?->can('update', $staff) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'verification_status' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
