<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffBankDetailData;
use App\Models\HR\Staff;
use App\Models\HR\StaffBankDetail;
use App\Repositories\Contracts\HR\StaffBankDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffBankDetailService
{
    public function __construct(
        protected StaffBankDetailRepositoryInterface $bankDetails,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->bankDetails->all($filters);
    }

    public function create(Staff $staff, StaffBankDetailData $data): StaffBankDetail
    {
        return DB::transaction(function () use ($staff, $data): StaffBankDetail {
            if (($data->attributes['is_primary'] ?? false) === true) {
                $staff->bankDetails()->update(['is_primary' => false]);
            }

            return $this->bankDetails->create($staff, $data);
        });
    }

    public function update(StaffBankDetail $bankDetail, StaffBankDetailData $data): StaffBankDetail
    {
        return DB::transaction(function () use ($bankDetail, $data): StaffBankDetail {
            if (($data->attributes['is_primary'] ?? false) === true) {
                $bankDetail->staff->bankDetails()->update(['is_primary' => false]);
            }

            return $this->bankDetails->update($bankDetail, $data);
        });
    }

    public function delete(StaffBankDetail $bankDetail): void
    {
        DB::transaction(fn (): bool => $bankDetail->delete());
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->bankDetails->allForStaff($staff);
    }
}
