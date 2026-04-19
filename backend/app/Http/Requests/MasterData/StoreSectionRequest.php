<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('students.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'class_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
