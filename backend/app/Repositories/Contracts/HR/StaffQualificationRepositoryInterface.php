<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffQualificationData;
use App\Models\HR\Staff;
use App\Models\HR\StaffQualification;
use Illuminate\Database\Eloquent\Collection;

interface StaffQualificationRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(Staff $staff, StaffQualificationData $data): StaffQualification;

    public function update(StaffQualification $qualification, StaffQualificationData $data): StaffQualification;

    public function delete(StaffQualification $qualification): void;

    public function allForStaff(Staff $staff): Collection;
}
