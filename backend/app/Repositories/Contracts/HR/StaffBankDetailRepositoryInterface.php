<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffBankDetailData;
use App\Models\HR\Staff;
use App\Models\HR\StaffBankDetail;
use Illuminate\Database\Eloquent\Collection;

interface StaffBankDetailRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(Staff $staff, StaffBankDetailData $data): StaffBankDetail;

    public function update(StaffBankDetail $bankDetail, StaffBankDetailData $data): StaffBankDetail;

    public function delete(StaffBankDetail $bankDetail): void;

    public function allForStaff(Staff $staff): Collection;
}
