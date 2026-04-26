<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMessageTemplateRequest extends CommunicationRequest
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
                Rule::unique('message_templates', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'template_type' => ['required', 'string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'variables' => ['nullable', 'array'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('template_type') === 'email' && ! $this->filled('subject')) {
                $validator->errors()->add('subject', 'The subject field is required for email templates.');
            }
        });
    }
}
