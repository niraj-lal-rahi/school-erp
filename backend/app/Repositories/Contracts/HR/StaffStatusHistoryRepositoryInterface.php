<?php

namespace App\Repositories\Contracts\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffStatusHistory;
use Illuminate\Database\Eloquent\Collection;

interface StaffStatusHistoryRepositoryInterface
{
    public function create(Staff $staff, array $attributes): StaffStatusHistory;

    public function allForStaff(Staff $staff): Collection;
}
