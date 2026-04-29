<?php

namespace App\Services\Examination;

use App\Events\Examination\ResultsComputed;
use App\Models\Examination\Exam;
use App\Models\Examination\ExamMark;
use App\Models\Examination\ExamSubject;
use App\Models\Examination\StudentExamEnrollment;
use App\Models\Examination\StudentResult;
use App\Repositories\Eloquent\Examination\ExamMarkRepository;
use App\Repositories\Eloquent\Examination\StudentResultRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResultComputationService
{
    public function __construct(
        protected StudentResultRepository $results,
        protected ExamMarkRepository $marks,
        protected GradingService $grading,
        protected MeritListService $meritList,
    ) {
    }

    public function computeForExam(Exam $exam, ?int $gradingSystemId = null): Collection
    {
        if (in_array($exam->result_status, ['published', 'archived'], true)) {
            throw ValidationException::withMessages([
                'exam' => 'Published or archived exam results cannot be recomputed.',
            ]);
        }

        return DB::transaction(function () use ($exam, $gradingSystemId): Collection {
            $exam->loadMissing([
                'examSubjects.subject',
                'studentExamEnrollments.student',
            ]);

            if ($exam->examSubjects->isEmpty()) {
                throw ValidationException::withMessages([
                    'exam_subjects' => 'At least one subject must be attached before results can be computed.',
                ]);
            }

            $gradingSystem = $this->grading->activeSystem($gradingSystemId, $exam->school_id);
            $marksByStudentSubject = $this->groupMarks($this->marks->byExam($exam->id));

            $processedStudents = collect();

            foreach ($exam->studentExamEnrollments as $enrollment) {
                if ($enrollment->status === 'cancelled') {
                    continue;
                }

                $subjectRows = $this->buildSubjectRows($exam, $enrollment, $marksByStudentSubject, $gradingSystem->id);
                $summary = $this->summarizeResult($exam, $subjectRows, $gradingSystem->id);

                $studentResult = $this->persistStudentResult($exam, $enrollment, $summary, $subjectRows);
                $processedStudents->push($studentResult);
            }

            $exam->update(['result_status' => 'processing']);
            $ranked = $this->meritList->assignRanks($exam);

            event(new ResultsComputed($exam->fresh(), $gradingSystem->id));

            return $ranked->whereIn('student_id', $processedStudents->pluck('student_id'))->values();
        });
    }

    protected function groupMarks(Collection $marks): Collection
    {
        return $marks->keyBy(function (ExamMark $mark): string {
            return $this->markKey((int) $mark->student_id, (int) $mark->subject_id);
        });
    }

    protected function buildSubjectRows(
        Exam $exam,
        StudentExamEnrollment $enrollment,
        Collection $marksByStudentSubject,
        int $gradingSystemId
    ): Collection {
        return $exam->examSubjects->map(function (ExamSubject $examSubject) use ($exam, $enrollment, $marksByStudentSubject, $gradingSystemId): array {
            $mark = $marksByStudentSubject->get($this->markKey((int) $enrollment->student_id, (int) $examSubject->subject_id));

            if (! $mark) {
                throw ValidationException::withMessages([
                    'marks' => sprintf(
                        'Marks are missing for student %s in subject %s.',
                        $enrollment->student?->full_name ?? $enrollment->student_id,
                        $examSubject->subject?->name ?? $examSubject->subject_id
                    ),
                ]);
            }

            $obtainedMarks = $mark->is_absent ? 0.0 : (float) $mark->marks_obtained;
            $subjectPercentage = (float) $examSubject->max_marks > 0
                ? round(($obtainedMarks / (float) $examSubject->max_marks) * 100, 2)
                : 0.0;
            $grading = $this->grading->mapPercentage($subjectPercentage, gradingSystemId: $gradingSystemId, schoolId: $exam->school_id);
            $subjectPassingMarks = (float) ($examSubject->passing_marks ?? 0);
            $isPass = ! $mark->is_absent && $obtainedMarks >= $subjectPassingMarks;

            return [
                'subject_id' => (int) $examSubject->subject_id,
                'max_marks' => (float) $examSubject->max_marks,
                'obtained_marks' => $obtainedMarks,
                'grade' => $grading['grade'],
                'gpa' => $grading['gpa'],
                'is_pass' => $isPass,
                'is_absent' => (bool) $mark->is_absent,
            ];
        });
    }

    protected function summarizeResult(Exam $exam, Collection $subjectRows, int $gradingSystemId): array
    {
        $totalMarks = (float) $subjectRows->sum('max_marks');
        $obtainedMarks = (float) $subjectRows->sum('obtained_marks');
        $percentage = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0.0;
        $overallGrading = $this->grading->mapPercentage($percentage, gradingSystemId: $gradingSystemId, schoolId: $exam->school_id);
        $allAbsent = $subjectRows->every(fn (array $row): bool => $row['is_absent'] === true);
        $allPassed = $subjectRows->every(fn (array $row): bool => $row['is_pass'] === true);

        $resultStatus = $allAbsent
            ? 'absent'
            : ($allPassed && $overallGrading['is_pass'] ? 'pass' : 'fail');

        return [
            'total_marks' => $totalMarks,
            'obtained_marks' => $obtainedMarks,
            'percentage' => $percentage,
            'grade' => $overallGrading['grade'],
            'gpa' => $overallGrading['gpa'],
            'result_status' => $resultStatus,
            'remarks' => $resultStatus === 'pass' ? 'Result computed successfully.' : null,
        ];
    }

    protected function persistStudentResult(
        Exam $exam,
        StudentExamEnrollment $enrollment,
        array $summary,
        Collection $subjectRows
    ): StudentResult {
        $existing = $this->results->firstForExamStudent($exam->id, (int) $enrollment->student_id);

        $payload = [
            'school_id' => $exam->school_id,
            'exam_id' => $exam->id,
            'student_id' => $enrollment->student_id,
            'total_marks' => $summary['total_marks'],
            'obtained_marks' => $summary['obtained_marks'],
            'percentage' => $summary['percentage'],
            'grade' => $summary['grade'],
            'gpa' => $summary['gpa'],
            'result_status' => $summary['result_status'],
            'rank' => null,
            'remarks' => $summary['remarks'],
            'computed_at' => now(),
        ];

        $studentResult = $existing
            ? $this->results->update($existing, $payload)
            : $this->results->create($payload);

        $this->results->deleteSubjectDetailsForResult($studentResult->id);

        foreach ($subjectRows as $row) {
            $this->results->createSubjectDetail([
                'school_id' => $exam->school_id,
                'student_result_id' => $studentResult->id,
                'subject_id' => $row['subject_id'],
                'max_marks' => $row['max_marks'],
                'obtained_marks' => $row['obtained_marks'],
                'grade' => $row['grade'],
                'is_pass' => $row['is_pass'],
            ]);
        }

        return $this->results->findOrFail($studentResult->id);
    }

    protected function markKey(int $studentId, int $subjectId): string
    {
        return $studentId.'-'.$subjectId;
    }
}
