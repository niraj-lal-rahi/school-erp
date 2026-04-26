<?php

namespace App\Http\Requests\Communication;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMessageTemplateRequest extends CommunicationRequest
{
    public function rules(): array
    {
        $schoolId = $this->tenantId();
        $templateId = $this->routeModelId('template');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('message_templates', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($templateId),
            ],
            'template_type' => ['sometimes', 'required', 'string', Rule::in(['email', 'sms', 'push', 'in_app'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
            'variables' => ['nullable', 'array'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('template_type');

            if ($type === null) {
                $template = $this->route('template');
                $type = is_object($template) ? $template->template_type : null;
            }

            if ($type === 'email' && ! $this->filled('subject') && ! filled($this->route('template')?->subject)) {
                $validator->errors()->add('subject', 'The subject field is required for email templates.');
            }
        });
    }
}
