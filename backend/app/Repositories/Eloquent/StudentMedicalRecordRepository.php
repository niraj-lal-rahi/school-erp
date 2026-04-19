<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentMedicalRecordData;
use App\Models\Student;
use App\Models\StudentMedicalRecord;
use App\Repositories\Contracts\StudentMedicalRecordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudentMedicalRecordRepository implements StudentMedicalRecordRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $medicalQuery) use ($search): void {
                    $medicalQuery->where('doctor_name', 'like', "%{$search}%")
                        ->orWhere('hospital_name', 'like', "%{$search}%")
                        ->orWhereHas('student', function (Builder $studentQuery) use ($search): void {
                            $studentQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('admission_no', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): StudentMedicalRecord
    {
        return $this->query()->findOrFail($id);
    }

    public function create(Student $student, StudentMedicalRecordData $data): StudentMedicalRecord
    {
        return $student->medicalRecords()->create($data->attributes);
    }

    public function update(StudentMedicalRecord $record, StudentMedicalRecordData $data): StudentMedicalRecord
    {
        $record->update($data->attributes);

        return $this->findOrFail($record->id);
    }

    public function delete(StudentMedicalRecord $record): void
    {
        $record->delete();
    }

    public function latestForStudent(Student $student): ?StudentMedicalRecord
    {
        return $this->query()
            ->where('student_id', $student->id)
            ->latest('id')
            ->first();
    }

    protected function query(): Builder
    {
        return StudentMedicalRecord::query()->with(['student']);
    }
}
