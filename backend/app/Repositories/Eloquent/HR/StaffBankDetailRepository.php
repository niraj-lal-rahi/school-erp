<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffBankDetailData;
use App\Models\HR\Staff;
use App\Models\HR\StaffBankDetail;
use App\Repositories\Contracts\HR\StaffBankDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffBankDetailRepository implements StaffBankDetailRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffBankDetail::query()
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get();
    }

    public function create(Staff $staff, StaffBankDetailData $data): StaffBankDetail
    {
        return $staff->bankDetails()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffBankDetail $bankDetail, StaffBankDetailData $data): StaffBankDetail
    {
        $bankDetail->update($data->attributes);

        return $bankDetail->refresh();
    }

    public function delete(StaffBankDetail $bankDetail): void
    {
        $bankDetail->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->all(['staff_id' => $staff->id]);
    }
}
