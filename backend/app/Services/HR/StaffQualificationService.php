<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffQualificationData;
use App\Models\HR\Staff;
use App\Models\HR\StaffQualification;
use App\Repositories\Contracts\HR\StaffQualificationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffQualificationService
{
    public function __construct(
        protected StaffQualificationRepositoryInterface $qualifications,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->qualifications->all($filters);
    }

    public function create(Staff $staff, StaffQualificationData $data): StaffQualification
    {
        return DB::transaction(fn (): StaffQualification => $this->qualifications->create($staff, $data));
    }

    public function update(StaffQualification $qualification, StaffQualificationData $data): StaffQualification
    {
        return DB::transaction(fn (): StaffQualification => $this->qualifications->update($qualification, $data));
    }

    public function delete(StaffQualification $qualification): void
    {
        DB::transaction(fn (): bool => $qualification->delete());
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->qualifications->allForStaff($staff);
    }
}
