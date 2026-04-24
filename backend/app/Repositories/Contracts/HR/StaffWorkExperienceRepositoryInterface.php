<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffWorkExperienceData;
use App\Models\HR\Staff;
use App\Models\HR\StaffWorkExperience;
use Illuminate\Database\Eloquent\Collection;

interface StaffWorkExperienceRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(Staff $staff, StaffWorkExperienceData $data): StaffWorkExperience;

    public function update(StaffWorkExperience $experience, StaffWorkExperienceData $data): StaffWorkExperience;

    public function delete(StaffWorkExperience $experience): void;

    public function allForStaff(Staff $staff): Collection;
}
