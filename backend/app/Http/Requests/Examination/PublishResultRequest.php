<?php

namespace App\Http\Requests\Examination;

class PublishResultRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'is_public' => ['sometimes', 'boolean'],
            'notify_users' => ['sometimes', 'boolean'],
        ];
    }
}
