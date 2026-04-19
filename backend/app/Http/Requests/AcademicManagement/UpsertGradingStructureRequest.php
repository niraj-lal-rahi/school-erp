<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\AcademicManagement\GradingStructure;
use Illuminate\Validation\Rule;

class UpsertGradingStructureRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var GradingStructure|null $gradingStructure */
        $gradingStructure = $this->route('gradingStructure');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('grading_structures', 'name')
                    ->where('school_id', $this->tenantId())
                    ->where('academic_year_id', $this->integer('academic_year_id'))
                    ->ignore($gradingStructure?->id),
            ],
            'description' => ['nullable', 'string'],
            'pass_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
            'scale_items' => ['required', 'array', 'min:1'],
            'scale_items.*.grade_label' => ['required', 'string', 'max:20'],
            'scale_items.*.min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale_items.*.max_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'scale_items.*.grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'scale_items.*.remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                foreach ($this->input('scale_items', []) as $index => $item) {
                    if (($item['min_percentage'] ?? 0) > ($item['max_percentage'] ?? 0)) {
                        $validator->errors()->add("scale_items.{$index}.min_percentage", 'Minimum percentage cannot be greater than maximum percentage.');
                    }
                }
            },
        ];
    }
}
