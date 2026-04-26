<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;

class CompleteTransportTripRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'completed_at' => ['nullable', 'date'],
            'completed_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
