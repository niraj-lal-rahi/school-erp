<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendanceStatusTypeData;
use App\Models\Attendance\AttendanceStatusType;
use App\Repositories\Contracts\Attendance\AttendanceStatusTypeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceStatusTypeRepository implements AttendanceStatusTypeRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendanceStatusType::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($statusQuery) use ($search): void {
                    $statusQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function create(AttendanceStatusTypeData $data): AttendanceStatusType
    {
        return AttendanceStatusType::create($data->attributes);
    }

    public function update(AttendanceStatusType $attendanceStatusType, AttendanceStatusTypeData $data): AttendanceStatusType
    {
        $attendanceStatusType->update($data->attributes);

        return $attendanceStatusType->refresh();
    }

    public function delete(AttendanceStatusType $attendanceStatusType): void
    {
        $attendanceStatusType->delete();
    }
}
