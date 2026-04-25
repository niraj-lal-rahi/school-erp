<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendanceSummaryData;
use App\Models\Attendance\AttendanceSummary;
use App\Repositories\Contracts\Attendance\AttendanceSummaryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceSummaryRepository implements AttendanceSummaryRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendanceSummary::query()
            ->with('academicYear')
            ->when($filters['user_type'] ?? null, fn ($query, string $type) => $query->where('user_type', $type))
            ->when($filters['user_id'] ?? null, fn ($query, int|string $id) => $query->where('user_id', $id))
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $id) => $query->where('academic_year_id', $id))
            ->orderByDesc('percentage')
            ->orderBy('user_type')
            ->get();
    }

    public function upsert(AttendanceSummaryData $data): AttendanceSummary
    {
        AttendanceSummary::query()->updateOrCreate(
            [
                'school_id' => $data->attributes['school_id'],
                'user_type' => $data->attributes['user_type'],
                'user_id' => $data->attributes['user_id'],
                'academic_year_id' => $data->attributes['academic_year_id'],
            ],
            $data->attributes,
        );

        return AttendanceSummary::query()
            ->where('school_id', $data->attributes['school_id'])
            ->where('user_type', $data->attributes['user_type'])
            ->where('user_id', $data->attributes['user_id'])
            ->where('academic_year_id', $data->attributes['academic_year_id'])
            ->firstOrFail();
    }
}
