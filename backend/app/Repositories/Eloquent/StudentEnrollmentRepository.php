<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\EnrollmentData;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentEnrollmentRepository implements StudentEnrollmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $enrollmentQuery) use ($search): void {
                    $enrollmentQuery->where('roll_number', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['school_class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('school_class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StudentEnrollment
    {
        return $this->query()->findOrFail($id);
    }

    public function create(EnrollmentData $data): StudentEnrollment
    {
        return StudentEnrollment::create($data->attributes);
    }

    public function update(StudentEnrollment $enrollment, EnrollmentData $data): StudentEnrollment
    {
        $enrollment->update($data->attributes);

        return $this->findOrFail($enrollment->id);
    }

    public function delete(StudentEnrollment $enrollment): void
    {
        $enrollment->delete();
    }

    public function currentForStudent(Student $student): ?StudentEnrollment
    {
        return $this->query()
            ->where('student_id', $student->id)
            ->where('is_current', true)
            ->latest('id')
            ->first();
    }

    public function allForStudent(Student $student): Collection
    {
        return $this->query()
            ->where('student_id', $student->id)
            ->get();
    }

    protected function query(): Builder
    {
        return StudentEnrollment::query()->with(['student', 'academicYear', 'schoolClass', 'section']);
    }
}
