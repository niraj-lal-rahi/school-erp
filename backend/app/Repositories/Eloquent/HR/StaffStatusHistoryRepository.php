<?php

namespace App\Repositories\Eloquent\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffStatusHistory;
use App\Repositories\Contracts\HR\StaffStatusHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffStatusHistoryRepository implements StaffStatusHistoryRepositoryInterface
{
    public function create(Staff $staff, array $attributes): StaffStatusHistory
    {
        return $staff->statusHistory()->create($attributes + ['school_id' => $staff->school_id]);
    }

    public function allForStaff(Staff $staff): Collection
    {
        return StaffStatusHistory::query()
            ->with('performer')
            ->where('staff_id', $staff->id)
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->get();
    }
}
