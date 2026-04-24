<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class ReviewStaffLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('hr.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'review_remarks' => ['nullable', 'string'],
        ];
    }
}
