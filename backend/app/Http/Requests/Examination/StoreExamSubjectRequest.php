<?php

namespace App\Http\Requests\Examination;

use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\Examination\Exam;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExamSubjectRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', $this->existsInTenant('exams')],
            'subject_id' => ['required', 'integer', $this->existsInTenant('subjects')],
            'max_marks' => ['required', 'numeric', 'min:0.01'],
            'passing_marks' => ['nullable', 'numeric', 'min:0'],
            'weightage' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->filled('passing_marks') && (float) $this->input('passing_marks') > (float) $this->input('max_marks')) {
                    $validator->errors()->add('passing_marks', 'Passing marks cannot be greater than max marks.');
                }

                $exists = \App\Models\Examination\ExamSubject::query()
                    ->where('exam_id', $this->integer('exam_id'))
                    ->where('subject_id', $this->integer('subject_id'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('subject_id', 'This subject has already been mapped to the selected exam.');
                }

                $exam = Exam::query()->find($this->integer('exam_id'));

                if (! $exam || ! $exam->class_id) {
                    return;
                }

                $subjectAssigned = ClassSubjectAssignment::query()
                    ->where('school_id', $this->tenantId())
                    ->where('academic_year_id', $exam->academic_year_id)
                    ->where('school_class_id', $exam->class_id)
                    ->where('subject_id', $this->integer('subject_id'))
                    ->when($exam->section_id, fn ($query) => $query->where(function ($builder) use ($exam): void {
                        $builder->whereNull('section_id')
                            ->orWhere('section_id', $exam->section_id);
                    }))
                    ->exists();

                if (! $subjectAssigned) {
                    $validator->errors()->add('subject_id', 'The selected subject is not assigned to the exam class or section.');
                }
            },
        ];
    }
}
