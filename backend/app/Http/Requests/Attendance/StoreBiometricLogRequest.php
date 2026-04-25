<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBiometricLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', 'string', Rule::in(['student', 'staff'])],
            'user_id' => ['required', 'integer'],
            'log_datetime' => ['required', 'date'],
            'log_type' => ['required', 'string', Rule::in(['check_in', 'check_out'])],
            'raw_data' => ['nullable', 'array'],
        ];
    }
}
