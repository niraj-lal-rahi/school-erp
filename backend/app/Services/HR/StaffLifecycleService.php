<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffStatusActionData;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\StaffStatusHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffLifecycleService
{
    public function __construct(
        protected StaffStatusHistoryRepositoryInterface $history,
    ) {
    }

    public function activate(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return $this->transition($staff, $data, $performedBy);
    }

    public function suspend(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return $this->transition($staff, $data, $performedBy);
    }

    public function resign(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return $this->transition($staff, $data, $performedBy);
    }

    public function terminate(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return $this->transition($staff, $data, $performedBy);
    }

    public function retire(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return $this->transition($staff, $data, $performedBy);
    }

    public function historyForStaff(Staff $staff): Collection
    {
        return $this->history->allForStaff($staff);
    }

    protected function transition(Staff $staff, StaffStatusActionData $data, int $performedBy): Staff
    {
        return DB::transaction(function () use ($staff, $data, $performedBy): Staff {
            $previousStatus = $staff->current_status;

            $staff->update([
                'current_status' => $data->newStatus,
                'updated_by' => $performedBy,
                'leaving_date' => in_array($data->newStatus, ['resigned', 'terminated', 'retired'], true)
                    ? ($data->effectiveDate ?? now()->toDateString())
                    : $staff->leaving_date,
            ]);

            $this->history->create($staff, [
                'previous_status' => $previousStatus,
                'new_status' => $data->newStatus,
                'action_type' => $data->actionType,
                'reason' => $data->reason,
                'effective_date' => $data->effectiveDate ?? now()->toDateString(),
                'performed_by' => $performedBy,
            ]);

            return $staff->refresh();
        });
    }
}
