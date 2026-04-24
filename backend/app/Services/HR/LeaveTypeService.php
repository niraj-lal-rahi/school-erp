<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\LeaveTypeData;
use App\Models\HR\LeaveType;
use App\Repositories\Contracts\HR\LeaveTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LeaveTypeService
{
    public function __construct(
        protected LeaveTypeRepositoryInterface $leaveTypes,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->leaveTypes->all($filters);
    }

    public function create(LeaveTypeData $data): LeaveType
    {
        return DB::transaction(fn (): LeaveType => $this->leaveTypes->create($data));
    }

    public function update(LeaveType $leaveType, LeaveTypeData $data): LeaveType
    {
        return DB::transaction(fn (): LeaveType => $this->leaveTypes->update($leaveType, $data));
    }

    public function delete(LeaveType $leaveType): void
    {
        DB::transaction(fn (): bool => $leaveType->delete());
    }
}
