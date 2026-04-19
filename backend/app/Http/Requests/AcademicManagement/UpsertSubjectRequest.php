<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Enums\AcademicManagement\SubjectType;
use App\Models\AcademicManagement\Subject;
use Illuminate\Validation\Rule;

class UpsertSubjectRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var Subject|null $subject */
        $subject = $this->route('subject');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $this->tenantId())
                    ->ignore($subject?->id),
            ],
            'type' => ['required', Rule::enum(SubjectType::class)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }
}
