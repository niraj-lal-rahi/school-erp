<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCommunicationMessageRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'conversation_id' => ['nullable', 'integer', $this->existsInTenant('communication_conversations')],
            'recipient_type' => ['required', 'string', Rule::in(['student', 'guardian', 'staff', 'user', 'group'])],
            'recipient_id' => ['required', 'integer'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'message_type' => ['required', 'string', Rule::in(['direct', 'group', 'system', 'notification'])],
            'priority' => ['nullable', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status' => ['nullable', 'string', Rule::in(['draft', 'sent', 'delivered', 'read', 'failed', 'archived'])],
            'channel' => ['nullable', 'string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $channels = $this->input('channels', []);

            if ($this->filled('channel')) {
                $channels[] = $this->input('channel');
            }

            if (in_array('email', $channels, true) && ! $this->filled('subject')) {
                $validator->errors()->add('subject', 'The subject field is required when sending email messages.');
            }

            $table = match ($this->input('recipient_type')) {
                'student' => 'students',
                'guardian' => 'guardians',
                'staff' => 'staff',
                'user' => 'users',
                'group' => 'communication_groups',
                default => null,
            };

            if ($table !== null) {
                $exists = \DB::table($table)
                    ->where('school_id', $this->tenantId())
                    ->where('id', $this->input('recipient_id'))
                    ->exists();

                if (! $exists) {
                    $validator->errors()->add('recipient_id', 'The selected recipient is invalid for the current tenant.');
                }
            }
        });
    }
}
