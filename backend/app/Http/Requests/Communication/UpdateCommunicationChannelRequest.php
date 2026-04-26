<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;

class UpdateCommunicationChannelRequest extends CommunicationRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $channelId = $this->routeModelId('channel');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('communication_channels', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($channelId),
            ],
            'channel_type' => ['sometimes', 'required', 'string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'provider' => ['nullable', 'string', 'max:255'],
            'configuration' => ['nullable', 'array'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
