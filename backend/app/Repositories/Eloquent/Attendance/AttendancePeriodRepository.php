<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendancePeriodData;
use App\Models\Attendance\AttendancePeriod;
use App\Repositories\Contracts\Attendance\AttendancePeriodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendancePeriodRepository implements AttendancePeriodRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendancePeriod::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($periodQuery) use ($search): void {
                    $periodQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('sequence')
            ->orderBy('name')
            ->get();
    }

    public function create(AttendancePeriodData $data): AttendancePeriod
    {
        return AttendancePeriod::create($data->attributes);
    }

    public function update(AttendancePeriod $attendancePeriod, AttendancePeriodData $data): AttendancePeriod
    {
        $attendancePeriod->update($data->attributes);

        return $attendancePeriod->refresh();
    }

    public function delete(AttendancePeriod $attendancePeriod): void
    {
        $attendancePeriod->delete();
    }
}
