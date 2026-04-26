<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;

class StartTransportTripRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'started_at' => ['nullable', 'date'],
            'started_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
