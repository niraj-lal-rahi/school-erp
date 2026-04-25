<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\AttendanceHolidayData;
use App\Models\Attendance\AttendanceHoliday;
use App\Repositories\Contracts\Attendance\AttendanceHolidayRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceHolidayService
{
    public function __construct(
        protected AttendanceHolidayRepositoryInterface $holidays,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->holidays->all($filters);
    }

    public function create(AttendanceHolidayData $data): AttendanceHoliday
    {
        $this->validateScope($data->attributes);

        return DB::transaction(fn (): AttendanceHoliday => $this->holidays->create($data));
    }

    public function update(AttendanceHoliday $holiday, AttendanceHolidayData $data): AttendanceHoliday
    {
        $this->validateScope($data->attributes);

        return DB::transaction(fn (): AttendanceHoliday => $this->holidays->update($holiday, $data));
    }

    public function delete(AttendanceHoliday $holiday): void
    {
        DB::transaction(function () use ($holiday): void {
            $this->holidays->delete($holiday);
        });
    }

    public function isHolidayForStudents(int $schoolId, int $academicYearId, string $date, ?int $classId = null, ?int $sectionId = null): bool
    {
        return AttendanceHoliday::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->where(function ($query) use ($classId, $sectionId): void {
                $query->whereIn('applies_to', ['all', 'students'])
                    ->orWhere(function ($classQuery) use ($classId, $sectionId): void {
                        $classQuery->where('applies_to', 'class')
                            ->where('school_class_id', $classId)
                            ->when($sectionId, fn ($q) => $q->where(function ($sectionScope) use ($sectionId): void {
                                $sectionScope->whereNull('section_id')->orWhere('section_id', $sectionId);
                            }));
                    })
                    ->orWhere(function ($sectionQuery) use ($sectionId): void {
                        $sectionQuery->where('applies_to', 'section')
                            ->where('section_id', $sectionId);
                    });
            })
            ->exists();
    }

    public function isHolidayForStaff(int $schoolId, int $academicYearId, string $date): bool
    {
        return AttendanceHoliday::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereIn('applies_to', ['all', 'staff'])
            ->exists();
    }

    protected function validateScope(array $attributes): void
    {
        if (($attributes['end_date'] ?? null) < ($attributes['start_date'] ?? null)) {
            throw ValidationException::withMessages([
                'end_date' => ['End date must be after or equal to start date.'],
            ]);
        }

        if (($attributes['applies_to'] ?? null) === 'class' && empty($attributes['school_class_id'])) {
            throw ValidationException::withMessages([
                'school_class_id' => ['A class is required when holiday scope is class.'],
            ]);
        }

        if (($attributes['applies_to'] ?? null) === 'section' && empty($attributes['section_id'])) {
            throw ValidationException::withMessages([
                'section_id' => ['A section is required when holiday scope is section.'],
            ]);
        }
    }
}
