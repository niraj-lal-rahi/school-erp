<?php

namespace App\Http\Requests\Transport;

use Illuminate\Validation\Rule;

class CancelTransportTripRequest extends TransportRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string'],
            'cancelled_by' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
