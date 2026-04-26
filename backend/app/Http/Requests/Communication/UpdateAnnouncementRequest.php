<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAnnouncementRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'integer', $this->existsInTenant('academic_years')],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'announcement_type' => ['sometimes', 'required', 'string', Rule::in(['general', 'academic', 'fee', 'attendance', 'exam', 'event', 'emergency'])],
            'audience_type' => ['sometimes', 'required', 'string', Rule::in(['all', 'students', 'parents', 'staff', 'teachers', 'class', 'section', 'individual'])],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'priority' => ['sometimes', 'required', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'scheduled', 'published', 'expired', 'cancelled'])],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'recipients' => ['nullable', 'array', 'min:1'],
            'recipients.*.recipient_type' => ['required_with:recipients', 'string', Rule::in(['student', 'guardian', 'staff', 'user'])],
            'recipients.*.recipient_id' => ['required_with:recipients', 'integer'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $storeRequest = new StoreAnnouncementRequest();
            $storeRequest->setContainer(app());
            $storeRequest->setRedirector(app('redirect'));
            $storeRequest->merge($this->all());
            $storeRequest->setUserResolver(fn () => $this->user());
            $storeRequest->setRouteResolver(fn () => $this->route());

            $storeRequest->withValidator($validator);
        });
    }
}
