<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendanceCorrectionData;
use App\Models\Attendance\AttendanceCorrection;
use App\Repositories\Contracts\Attendance\AttendanceCorrectionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceCorrectionRepository implements AttendanceCorrectionRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendanceCorrection::query()
            ->with(['oldStatus', 'newStatus', 'approver'])
            ->when($filters['reference_type'] ?? null, fn ($query, string $type) => $query->where('reference_type', $type))
            ->when($filters['reference_id'] ?? null, fn ($query, int|string $id) => $query->where('reference_id', $id))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['attendance_date_from'] ?? null, fn ($query, string $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when($filters['attendance_date_to'] ?? null, fn ($query, string $date) => $query->whereDate('attendance_date', '<=', $date))
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(AttendanceCorrectionData $data): AttendanceCorrection
    {
        return AttendanceCorrection::create($data->attributes)->load(['oldStatus', 'newStatus', 'approver']);
    }

    public function update(AttendanceCorrection $correction, AttendanceCorrectionData $data): AttendanceCorrection
    {
        $correction->update($data->attributes);

        return $correction->refresh()->load(['oldStatus', 'newStatus', 'approver']);
    }
}
