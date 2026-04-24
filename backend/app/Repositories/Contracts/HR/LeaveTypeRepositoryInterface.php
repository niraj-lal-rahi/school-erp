<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\LeaveTypeData;
use App\Models\HR\LeaveType;
use Illuminate\Database\Eloquent\Collection;

interface LeaveTypeRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(LeaveTypeData $data): LeaveType;

    public function update(LeaveType $leaveType, LeaveTypeData $data): LeaveType;

    public function delete(LeaveType $leaveType): void;
}
