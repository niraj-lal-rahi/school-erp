<?php

namespace App\Modules\SuperAdmin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartImpersonationRequest extends FormRequest
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
            'emergency_access' => ['sometimes', 'boolean'],
            'emergency_access_log_id' => ['sometimes', 'integer', 'min:1'],
            'ttl_minutes' => ['sometimes', 'integer', 'min:5', 'max:120'],
        ];
    }
}
