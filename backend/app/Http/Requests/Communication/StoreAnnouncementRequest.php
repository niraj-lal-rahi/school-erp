<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAnnouncementRequest extends CommunicationRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['nullable', 'integer', $this->existsInTenant('academic_years')],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'announcement_type' => ['required', 'string', Rule::in(['general', 'academic', 'fee', 'attendance', 'exam', 'event', 'emergency'])],
            'audience_type' => ['required', 'string', Rule::in(['all', 'students', 'parents', 'staff', 'teachers', 'class', 'section', 'individual'])],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'status' => ['nullable', 'string', Rule::in(['draft', 'scheduled', 'published', 'expired', 'cancelled'])],
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
            $this->validateAudienceRequirements($validator);
            $this->validateRecipientTargets($validator);
        });
    }

    protected function validateAudienceRequirements(Validator $validator): void
    {
        $audienceType = $this->input('audience_type');

        if ($audienceType === 'class' && ! $this->filled('class_id')) {
            $validator->errors()->add('class_id', 'The class_id field is required for class audience.');
        }

        if ($audienceType === 'section') {
            if (! $this->filled('class_id')) {
                $validator->errors()->add('class_id', 'The class_id field is required for section audience.');
            }

            if (! $this->filled('section_id')) {
                $validator->errors()->add('section_id', 'The section_id field is required for section audience.');
            }
        }

        if ($audienceType === 'individual' && ! is_array($this->input('recipients'))) {
            $validator->errors()->add('recipients', 'Recipient information is required for individual audience.');
        }

        if ($this->filled('section_id') && $this->filled('class_id')) {
            $sectionBelongsToClass = \DB::table('sections')
                ->where('school_id', $this->tenantId())
                ->where('id', $this->input('section_id'))
                ->where('class_id', $this->input('class_id'))
                ->exists();

            if (! $sectionBelongsToClass) {
                $validator->errors()->add('section_id', 'The selected section does not belong to the selected class.');
            }
        }
    }

    protected function validateRecipientTargets(Validator $validator): void
    {
        foreach ($this->input('recipients', []) as $index => $recipient) {
            $type = $recipient['recipient_type'] ?? null;
            $id = $recipient['recipient_id'] ?? null;

            if (! $type || ! $id) {
                continue;
            }

            $table = match ($type) {
                'student' => 'students',
                'guardian' => 'guardians',
                'staff' => 'staff',
                'user' => 'users',
                default => null,
            };

            if ($table === null) {
                continue;
            }

            $exists = \DB::table($table)
                ->where('school_id', $this->tenantId())
                ->where('id', $id)
                ->exists();

            if (! $exists) {
                $validator->errors()->add("recipients.{$index}.recipient_id", 'The selected recipient is invalid for the current tenant.');
            }
        }
    }
}
