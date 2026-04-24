<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffWorkExperienceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffWorkExperience;
use App\Repositories\Contracts\HR\StaffWorkExperienceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffWorkExperienceRepository implements StaffWorkExperienceRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffWorkExperience::query()
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();
    }

    public function create(Staff $staff, StaffWorkExperienceData $data): StaffWorkExperience
    {
        return $staff->workExperiences()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffWorkExperience $experience, StaffWorkExperienceData $data): StaffWorkExperience
    {
        $experience->update($data->attributes);

        return $experience->refresh();
    }

    public function delete(StaffWorkExperience $experience): void
    {
        $experience->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->all(['staff_id' => $staff->id]);
    }
}
