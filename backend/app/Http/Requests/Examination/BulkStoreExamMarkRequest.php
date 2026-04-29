<?php

namespace App\Http\Requests\Examination;

use App\Models\Examination\ExamMark;
use App\Models\Examination\ExamSubject;
use App\Models\Examination\StudentExamEnrollment;
use Illuminate\Validation\Validator;

class BulkStoreExamMarkRequest extends ExaminationRequest
{
    public function rules(): array
    {
        return [
            'exam_id' => ['required', 'integer', $this->existsInTenant('exams')],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', $this->existsInTenant('students')],
            'records.*.subject_id' => ['required', 'integer', $this->existsInTenant('subjects')],
            'records.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'records.*.is_absent' => ['sometimes', 'boolean'],
            'records.*.remarks' => ['nullable', 'string'],
            'records.*.evaluated_by' => ['nullable', 'integer', $this->existsInTenant('users')],
            'records.*.evaluated_at' => ['nullable', 'date'],
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
                $seenPairs = [];

                foreach ($this->input('records', []) as $index => $record) {
                    $studentId = (int) ($record['student_id'] ?? 0);
                    $subjectId = (int) ($record['subject_id'] ?? 0);
                    $marksObtained = $record['marks_obtained'] ?? null;
                    $isAbsent = (bool) ($record['is_absent'] ?? false);
                    $pairKey = $studentId.'-'.$subjectId;

                    if (isset($seenPairs[$pairKey])) {
                        $validator->errors()->add("records.{$index}.student_id", 'Duplicate student and subject combination found in the bulk payload.');
                        continue;
                    }

                    $seenPairs[$pairKey] = true;

                    $examSubject = ExamSubject::query()
                        ->where('school_id', $this->tenantId())
                        ->where('exam_id', $examId)
                        ->where('subject_id', $subjectId)
                        ->first();

                    if (! $examSubject) {
                        $validator->errors()->add("records.{$index}.subject_id", 'The selected subject is not mapped to the selected exam.');
                        continue;
                    }

                    $enrolled = StudentExamEnrollment::query()
                        ->where('school_id', $this->tenantId())
                        ->where('exam_id', $examId)
                        ->where('student_id', $studentId)
                        ->where('status', '!=', 'cancelled')
                        ->exists();

                    if (! $enrolled) {
                        $validator->errors()->add("records.{$index}.student_id", 'The selected student is not enrolled for this exam.');
                    }

                    if (! $isAbsent && ($marksObtained === null || $marksObtained === '')) {
                        $validator->errors()->add("records.{$index}.marks_obtained", 'Marks are required unless the student is marked absent.');
                    }

                    if ($marksObtained !== null && $marksObtained !== '' && (float) $marksObtained > (float) $examSubject->max_marks) {
                        $validator->errors()->add("records.{$index}.marks_obtained", 'Marks obtained cannot be greater than the subject max marks.');
                    }

                    if (ExamMark::query()
                        ->where('school_id', $this->tenantId())
                        ->where('exam_id', $examId)
                        ->where('student_id', $studentId)
                        ->where('subject_id', $subjectId)
                        ->exists()) {
                        $validator->errors()->add("records.{$index}.student_id", 'Marks have already been recorded for this student and subject in the selected exam.');
                    }
                }
            },
        ];
    }
}
