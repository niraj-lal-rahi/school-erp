<?php

namespace App\Http\Requests\Examination;

use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use Illuminate\Validation\Validator;

class StoreGradeScaleRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'grading_system_id' => ['required', 'integer', $this->existsInTenant('grading_systems')],
            'grade_label' => ['required', 'string', 'max:30'],
            'min_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'grade_point' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ((float) $this->input('min_percentage') > (float) $this->input('max_percentage')) {
                    $validator->errors()->add('min_percentage', 'Minimum percentage cannot be greater than maximum percentage.');
                }

                $gradingSystem = GradingSystem::query()->find($this->integer('grading_system_id'));

                if ($gradingSystem?->grading_type === 'gpa' && ! $this->filled('grade_point')) {
                    $validator->errors()->add('grade_point', 'Grade point is required for GPA grading systems.');
                }

                $overlapExists = GradeScale::query()
                    ->where('school_id', $this->tenantId())
                    ->where('grading_system_id', $this->integer('grading_system_id'))
                    ->where(function ($query): void {
                        $query->whereBetween('min_percentage', [(float) $this->input('min_percentage'), (float) $this->input('max_percentage')])
                            ->orWhereBetween('max_percentage', [(float) $this->input('min_percentage'), (float) $this->input('max_percentage')])
                            ->orWhere(function ($nested): void {
                                $nested->where('min_percentage', '<=', (float) $this->input('min_percentage'))
                                    ->where('max_percentage', '>=', (float) $this->input('max_percentage'));
                            });
                    })
                    ->exists();

                if ($overlapExists) {
                    $validator->errors()->add('min_percentage', 'This grade scale overlaps with an existing scale in the selected grading system.');
                }
            },
        ];
    }
}
