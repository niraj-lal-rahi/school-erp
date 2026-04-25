<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendanceHolidayData;
use App\Models\Attendance\AttendanceHoliday;
use App\Repositories\Contracts\Attendance\AttendanceHolidayRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceHolidayRepository implements AttendanceHolidayRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendanceHoliday::query()
            ->with(['academicYear', 'schoolClass', 'section'])
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $id) => $query->where('academic_year_id', $id))
            ->when($filters['class_id'] ?? null, fn ($query, int|string $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn ($query, int|string $id) => $query->where('section_id', $id))
            ->when($filters['applies_to'] ?? null, fn ($query, string $appliesTo) => $query->where('applies_to', $appliesTo))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($holidayQuery) use ($search): void {
                    $holidayQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('end_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('start_date', '<=', $date))
            ->orderBy('start_date')
            ->orderBy('title')
            ->get();
    }

    public function create(AttendanceHolidayData $data): AttendanceHoliday
    {
        return AttendanceHoliday::create($data->attributes)->load(['academicYear', 'schoolClass', 'section']);
    }

    public function update(AttendanceHoliday $holiday, AttendanceHolidayData $data): AttendanceHoliday
    {
        $holiday->update($data->attributes);

        return $holiday->refresh()->load(['academicYear', 'schoolClass', 'section']);
    }

    public function delete(AttendanceHoliday $holiday): void
    {
        $holiday->delete();
    }
}
