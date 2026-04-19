<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentData;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentRepository implements StudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Student::query()
            ->with(['guardians', 'enrollments.schoolClass', 'enrollments.section', 'admissions', 'documents'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($studentQuery) use ($search): void {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function create(StudentData $data): Student
    {
        $student = Student::create($data->studentAttributes());
        $student->guardians()->sync($data->guardianPivotData($student->school_id));
        $student->enrollments()->create($data->enrollmentAttributes($student->school_id));
        $student->admissions()->create($data->admissionAttributes($student->school_id));

        return $student->load(['guardians', 'enrollments.schoolClass', 'enrollments.section', 'admissions', 'documents']);
    }

    public function update(Student $student, StudentData $data): Student
    {
        $student->update($data->studentAttributes());
        $student->guardians()->sync($data->guardianPivotData($student->school_id));

        $enrollment = $student->enrollments()->latest('id')->first();
        if ($enrollment) {
            $enrollment->update($data->enrollmentAttributes($student->school_id));
        }

        $admission = $student->admissions()->latest('id')->first();
        if ($admission) {
            $admission->update($data->admissionAttributes($student->school_id));
        }

        return $student->refresh()->load(['guardians', 'enrollments.schoolClass', 'enrollments.section', 'admissions', 'documents']);
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }

    public function createDocument(Student $student, array $attributes): StudentDocument
    {
        return $student->documents()->create($attributes);
    }
}
