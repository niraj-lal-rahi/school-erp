<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;

class PublishAnnouncementRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'priority' => ['nullable', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['email', 'sms', 'push', 'in_app'])],
        ];
    }
}
