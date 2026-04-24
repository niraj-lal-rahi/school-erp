<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffQualificationData;
use App\Models\HR\Staff;
use App\Models\HR\StaffQualification;
use App\Repositories\Contracts\HR\StaffQualificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffQualificationRepository implements StaffQualificationRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffQualification::query()
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('passing_year')
            ->orderBy('degree')
            ->get();
    }

    public function create(Staff $staff, StaffQualificationData $data): StaffQualification
    {
        return $staff->qualifications()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffQualification $qualification, StaffQualificationData $data): StaffQualification
    {
        $qualification->update($data->attributes);

        return $qualification->refresh();
    }

    public function delete(StaffQualification $qualification): void
    {
        $qualification->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->all(['staff_id' => $staff->id]);
    }
}
