<?php

namespace App\Services\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\ExamMark;
use App\Models\Examination\ExamSubject;
use App\Repositories\Eloquent\Examination\ExamMarkRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarksEntryService
{
    public function __construct(
        protected ExamMarkRepository $marks,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->marks->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): ExamMark
    {
        return $this->marks->findOrFail($id);
    }

    public function store(array $attributes, ?int $evaluatorId = null): ExamMark
    {
        $exam = Exam::query()->with('examSubjects')->findOrFail($attributes['exam_id']);
        $this->ensureMarksEditable($exam);
        $subject = $this->resolveExamSubject($exam, (int) $attributes['subject_id']);

        return DB::transaction(function () use ($attributes, $evaluatorId, $exam, $subject): ExamMark {
            $existing = $this->marks->firstForExamStudentSubject(
                (int) $attributes['exam_id'],
                (int) $attributes['student_id'],
                (int) $attributes['subject_id']
            );

            $payload = $this->normalizeMarkPayload($attributes, $subject, $evaluatorId ?? auth()->id());

            if ($existing) {
                return $this->marks->update($existing, $payload);
            }

            return $this->marks->create([
                ...$payload,
                'school_id' => $exam->school_id,
            ]);
        });
    }

    public function bulkStore(array $rows, ?int $evaluatorId = null): Collection
    {
        return DB::transaction(function () use ($rows, $evaluatorId): Collection {
            $stored = collect();

            foreach ($rows as $row) {
                $stored->push($this->store($row, $evaluatorId));
            }

            return $stored;
        });
    }

    protected function normalizeMarkPayload(array $attributes, ExamSubject $subject, ?int $evaluatorId): array
    {
        $isAbsent = (bool) ($attributes['is_absent'] ?? false);
        $marksObtained = $isAbsent ? null : (float) $attributes['marks_obtained'];

        if (! $isAbsent && $marksObtained > (float) $subject->max_marks) {
            throw ValidationException::withMessages([
                'marks_obtained' => 'Marks obtained cannot exceed the maximum marks for the subject.',
            ]);
        }

        return [
            'exam_id' => (int) $attributes['exam_id'],
            'student_id' => (int) $attributes['student_id'],
            'subject_id' => (int) $attributes['subject_id'],
            'marks_obtained' => $marksObtained,
            'is_absent' => $isAbsent,
            'remarks' => $attributes['remarks'] ?? null,
            'evaluated_by' => $evaluatorId,
            'evaluated_at' => now(),
        ];
    }

    protected function resolveExamSubject(Exam $exam, int $subjectId): ExamSubject
    {
        $subject = $exam->examSubjects->firstWhere('subject_id', $subjectId);

        if (! $subject) {
            throw ValidationException::withMessages([
                'subject_id' => 'The selected subject is not mapped to this exam.',
            ]);
        }

        return $subject;
    }

    protected function ensureMarksEditable(Exam $exam): void
    {
        if (in_array($exam->result_status, ['published', 'archived'], true)) {
            throw ValidationException::withMessages([
                'exam' => 'Marks cannot be changed after results are published or archived.',
            ]);
        }
    }
}
