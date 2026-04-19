<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\AdmissionData;
use App\Models\Admission;
use App\Repositories\Contracts\AdmissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdmissionRepository implements AdmissionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $admissionQuery) use ($search): void {
                    $admissionQuery->where('application_no', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('guardian_name', 'like', "%{$search}%")
                        ->orWhere('guardian_phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('applied_class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value))
            ->when($filters['application_status'] ?? null, fn (Builder $query, string $value) => $query->where('application_status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Admission
    {
        return $this->query()->findOrFail($id);
    }

    public function create(AdmissionData $data): Admission
    {
        return Admission::create($data->attributes);
    }

    public function update(Admission $admission, AdmissionData $data): Admission
    {
        $admission->update($data->attributes);

        return $this->findOrFail($admission->id);
    }

    public function delete(Admission $admission): void
    {
        $admission->delete();
    }

    protected function query(): Builder
    {
        return Admission::query()->with(['academicYear', 'appliedClass', 'section', 'student', 'reviewer']);
    }
}
