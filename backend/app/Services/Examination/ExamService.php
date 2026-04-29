<?php

namespace App\Services\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\ExamSubject;
use App\Repositories\Eloquent\Examination\ExamRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function __construct(
        protected ExamRepository $exams,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->exams->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Exam
    {
        return $this->exams->findOrFail($id);
    }

    public function create(array $attributes): Exam
    {
        return DB::transaction(function () use ($attributes): Exam {
            $payload = $attributes;
            $payload['result_status'] = $payload['result_status'] ?? 'draft';
            $payload['created_by'] = $payload['created_by'] ?? auth()->id();

            return $this->exams->create($payload);
        });
    }

    public function update(Exam $exam, array $attributes): Exam
    {
        $this->ensureEditable($exam);

        return DB::transaction(function () use ($exam, $attributes): Exam {
            return $this->exams->update($exam, $attributes);
        });
    }

    public function delete(Exam $exam): void
    {
        $this->ensureEditable($exam);

        DB::transaction(function () use ($exam): void {
            $this->exams->delete($exam);
        });
    }

    public function attachSubject(Exam $exam, array $attributes): ExamSubject
    {
        $this->ensureEditable($exam);

        return DB::transaction(function () use ($exam, $attributes): ExamSubject {
            $subject = $this->exams->createSubject([
                ...$attributes,
                'school_id' => $exam->school_id,
                'exam_id' => $exam->id,
            ]);

            $this->refreshExamTotals($exam);

            return $subject;
        });
    }

    public function detachSubject(ExamSubject $examSubject): void
    {
        $examSubject->loadMissing('exam');
        $this->ensureEditable($examSubject->exam);

        DB::transaction(function () use ($examSubject): void {
            $exam = $examSubject->exam;

            $this->exams->deleteSubject($examSubject);
            $this->refreshExamTotals($exam);
        });
    }

    public function subjectsForExam(Exam $exam): Collection
    {
        return $this->exams->subjectsForExam($exam->id);
    }

    protected function ensureEditable(Exam $exam): void
    {
        if (in_array($exam->result_status, ['published', 'archived'], true)) {
            throw ValidationException::withMessages([
                'exam' => 'Published or archived exams cannot be modified.',
            ]);
        }
    }

    protected function refreshExamTotals(Exam $exam): void
    {
        $subjects = $this->exams->subjectsForExam($exam->id);

        $exam->update([
            'total_marks' => (float) $subjects->sum('max_marks'),
            'passing_marks' => $subjects->sum(function (ExamSubject $subject): float {
                return (float) ($subject->passing_marks ?? 0);
            }),
        ]);
    }
}
