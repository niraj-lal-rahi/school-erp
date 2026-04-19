<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Validation\Rule;

class UpsertSectionRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var Section|null $section */
        $section = $this->route('section');

        return [
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:50'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('sections', 'code')
                    ->where('school_id', $this->tenantId())
                    ->where('school_class_id', $this->integer('school_class_id'))
                    ->ignore($section?->id),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $schoolClass = SchoolClass::query()->find($this->integer('school_class_id'));
                if (! $schoolClass || $schoolClass->school_id !== $this->tenantId()) {
                    $validator->errors()->add('school_class_id', 'Section must belong to a class from the current tenant.');
                }
            },
        ];
    }
}
