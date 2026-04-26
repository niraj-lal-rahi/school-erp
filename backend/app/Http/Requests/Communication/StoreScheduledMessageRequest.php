<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreScheduledMessageRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'template_id' => ['nullable', 'integer', $this->existsInTenant('message_templates')],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'audience_type' => ['required', 'string', Rule::in(['all', 'students', 'parents', 'staff', 'teachers', 'class', 'section', 'individual'])],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'channel' => ['required', 'string', Rule::in(['email', 'sms', 'push', 'in_app', 'multi'])],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'processing', 'sent', 'failed', 'cancelled'])],
            'recipients' => ['nullable', 'array', 'min:1'],
            'recipients.*.recipient_type' => ['required_with:recipients', 'string', Rule::in(['student', 'guardian', 'staff', 'user'])],
            'recipients.*.recipient_id' => ['required_with:recipients', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $announcementRules = new StoreAnnouncementRequest();
            $announcementRules->setContainer(app());
            $announcementRules->setRedirector(app('redirect'));
            $announcementRules->merge([
                'audience_type' => $this->input('audience_type'),
                'class_id' => $this->input('class_id'),
                'section_id' => $this->input('section_id'),
                'recipients' => $this->input('recipients'),
            ]);
            $announcementRules->setUserResolver(fn () => $this->user());
            $announcementRules->setRouteResolver(fn () => $this->route());
            $announcementRules->withValidator($validator);

            if ($this->input('channel') === 'multi' && empty($this->input('channels', []))) {
                $validator->errors()->add('channels', 'At least one delivery channel is required when channel is multi.');
            }
        });
    }
}
