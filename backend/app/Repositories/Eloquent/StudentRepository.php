<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentData;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StudentRepository implements StudentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $studentQuery) use ($search): void {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_no', 'like', "%{$search}%")
                        ->orWhere('roll_no', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('guardians', function (Builder $guardianQuery) use ($search): void {
                            $guardianQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('current_status', $status))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int|string $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['house_id'] ?? null, fn (Builder $query, int|string $houseId) => $query->where('house_id', $houseId))
            ->when($filters['guardian_id'] ?? null, function (Builder $query, int|string $guardianId): void {
                $query->whereHas('guardians', fn (Builder $guardianQuery) => $guardianQuery->where('guardians.id', $guardianId));
            })
            ->when(
                ($filters['academic_year_id'] ?? null)
                || ($filters['school_class_id'] ?? null)
                || ($filters['section_id'] ?? null),
                function (Builder $query) use ($filters): void {
                    $query->whereHas('enrollments', function (Builder $enrollmentQuery) use ($filters): void {
                        $enrollmentQuery->where('is_current', true)
                            ->when($filters['academic_year_id'] ?? null, fn (Builder $q, int|string $value) => $q->where('academic_year_id', $value))
                            ->when($filters['school_class_id'] ?? null, fn (Builder $q, int|string $value) => $q->where('school_class_id', $value))
                            ->when($filters['section_id'] ?? null, fn (Builder $q, int|string $value) => $q->where('section_id', $value));
                    });
                }
            )
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Student
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function create(StudentData $data): Student
    {
        $student = Student::create($data->studentAttributes());
        $student->guardians()->sync($data->guardianPivotData($student->school_id));

        if ($data->enrollment !== []) {
            $student->enrollments()->create($data->enrollmentAttributes($student->school_id));
        }

        if ($data->admission !== []) {
            $student->admissions()->create($this->buildAdmissionAttributes($student, $data));
        }

        return $this->findOrFail($student->id);
    }

    public function update(Student $student, StudentData $data): Student
    {
        $student->update($data->studentAttributes());

        $student->guardians()->sync($data->guardianPivotData($student->school_id));

        if ($data->enrollment !== []) {
            $enrollment = $student->enrollments()->latest('id')->first();
            if ($enrollment) {
                $enrollment->update($data->enrollmentAttributes($student->school_id));
            }
        }

        if ($data->admission !== []) {
            $admission = $student->admissions()->latest('id')->first();
            if ($admission) {
                $admission->update($this->buildAdmissionAttributes($student, $data, $admission->application_no, $admission->uuid));
            }
        }

        return $this->findOrFail($student->id);
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }

    public function assignGuardians(Student $student, array $guardianPivotData, bool $detaching = false): Student
    {
        $student->guardians()->sync($guardianPivotData, $detaching);

        return $this->findOrFail($student->id);
    }

    public function removeGuardian(Student $student, int $guardianId): Student
    {
        $student->guardians()->detach($guardianId);

        return $this->findOrFail($student->id);
    }

    public function guardians(Student $student): Collection
    {
        return $student->guardians()->get();
    }

    public function createDocument(Student $student, array $attributes): StudentDocument
    {
        return $student->documents()->create($attributes);
    }

    protected function baseQuery(): Builder
    {
        return Student::query()->with([
            'guardians',
            'enrollments.schoolClass',
            'enrollments.section',
            'admissions',
            'documents',
            'latestMedicalRecord',
            'statusHistory.performer',
            'notesEntries.creator',
            'category',
            'house',
        ]);
    }

    protected function buildAdmissionAttributes(Student $student, StudentData $data, ?string $applicationNo = null, ?string $uuid = null): array
    {
        $primaryGuardian = collect($data->guardians)->firstWhere('is_primary', true) ?? collect($data->guardians)->first();
        $guardian = isset($primaryGuardian['id']) ? $student->guardians()->withoutGlobalScopes()->find($primaryGuardian['id']) : null;

        return [
            'school_id' => $student->school_id,
            'uuid' => $uuid ?? (string) Str::uuid(),
            'application_no' => $applicationNo ?? ('APP-'.$student->school_id.'-'.$student->admission_no),
            'student_id' => $student->id,
            'academic_year_id' => $data->admission['academic_year_id'] ?? $data->enrollment['academic_year_id'] ?? null,
            'applied_class_id' => $data->admission['applied_class_id'] ?? $data->enrollment['school_class_id'] ?? null,
            'section_id' => $data->enrollment['section_id'] ?? null,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'gender' => $student->gender,
            'date_of_birth' => optional($student->date_of_birth)->toDateString(),
            'guardian_name' => $guardian?->full_name,
            'guardian_phone' => $guardian?->phone,
            'guardian_email' => $guardian?->email,
            'address_line1' => data_get($student->address, 'line1'),
            'address_line2' => data_get($student->address, 'line2'),
            'city' => data_get($student->address, 'city'),
            'state' => data_get($student->address, 'state'),
            'country' => data_get($student->address, 'country'),
            'postal_code' => data_get($student->address, 'postal_code'),
            'status' => $data->admission['status'] ?? 'admitted',
            'application_status' => 'converted',
            'applied_on' => $data->admission['applied_on'] ?? optional($student->admission_date)->toDateString(),
            'admitted_on' => $data->admission['admitted_on'] ?? optional($student->admission_date)->toDateString(),
            'remarks' => $data->admission['remarks'] ?? null,
        ];
    }
}
