<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\LeaveTypeData;
use App\Models\HR\LeaveType;
use App\Repositories\Contracts\HR\LeaveTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeRepository implements LeaveTypeRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return LeaveType::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($leaveTypeQuery) use ($search): void {
                    $leaveTypeQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function create(LeaveTypeData $data): LeaveType
    {
        return LeaveType::create($data->attributes);
    }

    public function update(LeaveType $leaveType, LeaveTypeData $data): LeaveType
    {
        $leaveType->update($data->attributes);

        return $leaveType->refresh();
    }

    public function delete(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }
}
