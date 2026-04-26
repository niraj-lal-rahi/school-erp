<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;

class StoreCommunicationChannelRequest extends CommunicationRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('communication_channels', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'channel_type' => ['required', 'string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'provider' => ['nullable', 'string', 'max:255'],
            'configuration' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
