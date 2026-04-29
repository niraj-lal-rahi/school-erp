<?php

namespace App\Services\Examination;

use App\Models\Examination\Exam;
use App\Models\Examination\StudentExamEnrollment;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamEnrollmentService
{
    public function enrollForExam(Exam $exam): Collection
    {
        $this->ensureEnrollmentAllowed($exam);

        return DB::transaction(function () use ($exam): Collection {
            $enrollments = StudentEnrollment::query()
                ->with('student')
                ->where('school_id', $exam->school_id)
                ->where('academic_year_id', $exam->academic_year_id)
                ->where('school_class_id', $exam->class_id)
                ->when($exam->section_id, fn ($query) => $query->where('section_id', $exam->section_id))
                ->where('is_current', true)
                ->where('status', 'active')
                ->orderBy('student_id')
                ->get();

            if ($enrollments->isEmpty()) {
                throw ValidationException::withMessages([
                    'exam' => 'No active student enrollments were found for the selected class and section.',
                ]);
            }

            foreach ($enrollments as $enrollment) {
                StudentExamEnrollment::query()->withTrashed()->updateOrCreate(
                    [
                        'school_id' => $exam->school_id,
                        'exam_id' => $exam->id,
                        'student_id' => $enrollment->student_id,
                    ],
                    [
                        'class_id' => $enrollment->school_class_id,
                        'section_id' => $enrollment->section_id,
                        'roll_no' => $enrollment->roll_number,
                        'status' => 'enrolled',
                        'deleted_at' => null,
                    ]
                );
            }

            return StudentExamEnrollment::query()
                ->with(['student', 'schoolClass', 'section'])
                ->where('exam_id', $exam->id)
                ->orderBy('roll_no')
                ->orderBy('student_id')
                ->get();
        });
    }

    protected function ensureEnrollmentAllowed(Exam $exam): void
    {
        if (in_array($exam->result_status, ['published', 'archived'], true)) {
            throw ValidationException::withMessages([
                'exam' => 'Students cannot be enrolled after results are published or archived.',
            ]);
        }

        if (! $exam->class_id) {
            throw ValidationException::withMessages([
                'class_id' => 'A class is required before students can be enrolled for an exam.',
            ]);
        }
    }
}
