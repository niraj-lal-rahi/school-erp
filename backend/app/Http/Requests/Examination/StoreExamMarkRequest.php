<?php

namespace App\Http\Requests\Examination;

use App\Models\Examination\ExamMark;
use App\Models\Examination\ExamSubject;
use App\Models\Examination\StudentExamEnrollment;
use Illuminate\Validation\Validator;

class StoreExamMarkRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', $this->existsInTenant('exams')],
            'student_id' => ['required', 'integer', $this->existsInTenant('students')],
            'subject_id' => ['required', 'integer', $this->existsInTenant('subjects')],
            'marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'is_absent' => ['sometimes', 'boolean'],
            'remarks' => ['nullable', 'string'],
            'evaluated_by' => ['nullable', 'integer', $this->existsInTenant('users')],
            'evaluated_at' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $examId = $this->integer('exam_id');
                $studentId = $this->integer('student_id');
                $subjectId = $this->integer('subject_id');
                $isAbsent = (bool) $this->boolean('is_absent');

                $examSubject = ExamSubject::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('subject_id', $subjectId)
                    ->first();

                if (! $examSubject) {
                    $validator->errors()->add('subject_id', 'The selected subject is not mapped to the selected exam.');
                    return;
                }

                $enrolled = StudentExamEnrollment::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('student_id', $studentId)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if (! $enrolled) {
                    $validator->errors()->add('student_id', 'The selected student is not enrolled for this exam.');
                }

                if (! $isAbsent && ! $this->filled('marks_obtained')) {
                    $validator->errors()->add('marks_obtained', 'Marks are required unless the student is marked absent.');
                }

                if ($this->filled('marks_obtained') && (float) $this->input('marks_obtained') > (float) $examSubject->max_marks) {
                    $validator->errors()->add('marks_obtained', 'Marks obtained cannot be greater than the subject max marks.');
                }

                if (ExamMark::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->exists()) {
                    $validator->errors()->add('student_id', 'Marks have already been recorded for this student and subject in the selected exam.');
                }
            },
        ];
    }
}
