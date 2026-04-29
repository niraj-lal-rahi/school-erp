<?php

namespace App\Http\Requests\Examination;

use App\Models\Examination\ExamSubject;
use App\Models\Examination\RevaluationRequest;
use App\Models\Examination\StudentResult;
use Illuminate\Validation\Validator;

class StoreRevaluationRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', $this->existsInTenant('exams')],
            'student_id' => ['required', 'integer', $this->existsInTenant('students')],
            'subject_id' => ['required', 'integer', $this->existsInTenant('subjects')],
            'reason' => ['required', 'string'],
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['pending', 'approved', 'rejected', 'completed'])],
            'requested_at' => ['required', 'date'],
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

                $examSubject = ExamSubject::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if (! $examSubject) {
                    $validator->errors()->add('subject_id', 'The selected subject is not part of the selected exam.');
                }

                $resultExists = StudentResult::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('student_id', $studentId)
                    ->exists();

                if (! $resultExists) {
                    $validator->errors()->add('student_id', 'The selected student does not yet have a computed result for this exam.');
                }

                $duplicatePending = RevaluationRequest::query()
                    ->where('school_id', $this->tenantId())
                    ->where('exam_id', $examId)
                    ->where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($duplicatePending) {
                    $validator->errors()->add('subject_id', 'A revaluation request is already open for this student and subject.');
                }
            },
        ];
    }
}
