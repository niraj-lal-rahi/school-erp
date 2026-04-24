<?php

namespace App\Http\Requests\HR;

use App\Models\HR\StaffDocument;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('staffDocument');

        return $document instanceof StaffDocument
            ? ($this->user()?->can('update', $document->staff) ?? false)
            : false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'string', 'max:100'],
            'title' => ['sometimes', 'string', 'max:255'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issued_date'],
            'verification_status' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
