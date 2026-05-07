<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestEmergencyAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
            'ttl_minutes' => ['sometimes', 'integer', 'min:5', 'max:120'],
            'requires_second_admin_approval' => ['sometimes', 'boolean'],
        ];
    }
}
