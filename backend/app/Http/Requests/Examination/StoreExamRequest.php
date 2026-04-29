<?php

namespace App\Http\Requests\Examination;

use App\Models\Section;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExamRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', $this->existsInTenant('academic_years')],
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', $this->uniqueInTenant('exams', 'code')],
            'exam_type_id' => ['required', 'integer', $this->existsInTenant('exam_types')],
            'term_id' => ['nullable', 'integer', $this->existsInTenant('academic_terms')],
            'class_id' => ['nullable', 'integer', $this->existsInTenant('school_classes')],
            'section_id' => ['nullable', 'integer', $this->existsInTenant('sections')],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_marks' => ['nullable', 'numeric', 'min:0'],
            'passing_marks' => ['nullable', 'numeric', 'min:0'],
            'result_status' => ['required', 'string', Rule::in(['draft', 'processing', 'published', 'archived'])],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->filled('passing_marks') && $this->filled('total_marks')
                    && (float) $this->input('passing_marks') > (float) $this->input('total_marks')) {
                    $validator->errors()->add('passing_marks', 'Passing marks cannot be greater than total marks.');
                }

                if ($this->filled('section_id') && ! $this->filled('class_id')) {
                    $validator->errors()->add('class_id', 'Class is required when section is selected.');
                }

                if ($this->filled('section_id') && $this->filled('class_id')) {
                    $section = Section::query()->find($this->integer('section_id'));

                    if ($section && (int) $section->school_class_id !== $this->integer('class_id')) {
                        $validator->errors()->add('section_id', 'The selected section does not belong to the selected class.');
                    }
                }
            },
        ];
    }
}
