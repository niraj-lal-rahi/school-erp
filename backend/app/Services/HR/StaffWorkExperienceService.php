<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffWorkExperienceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffWorkExperience;
use App\Repositories\Contracts\HR\StaffWorkExperienceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffWorkExperienceService
{
    public function __construct(
        protected StaffWorkExperienceRepositoryInterface $experiences,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->experiences->all($filters);
    }

    public function create(Staff $staff, StaffWorkExperienceData $data): StaffWorkExperience
    {
        return DB::transaction(function () use ($staff, $data): StaffWorkExperience {
            if (($data->attributes['is_current'] ?? false) === true) {
                $this->clearCurrent($staff);
            }

            return $this->experiences->create($staff, $data);
        });
    }

    public function update(StaffWorkExperience $experience, StaffWorkExperienceData $data): StaffWorkExperience
    {
        return DB::transaction(function () use ($experience, $data): StaffWorkExperience {
            if (($data->attributes['is_current'] ?? false) === true) {
                $this->clearCurrent($experience->staff);
            }

            return $this->experiences->update($experience, $data);
        });
    }

    public function delete(StaffWorkExperience $experience): void
    {
        DB::transaction(fn (): bool => $experience->delete());
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->experiences->allForStaff($staff);
    }

    protected function clearCurrent(Staff $staff): void
    {
        $staff->workExperiences()->update(['is_current' => false]);
    }
}
