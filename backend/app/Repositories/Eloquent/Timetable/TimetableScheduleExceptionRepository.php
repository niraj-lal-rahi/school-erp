<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetableScheduleExceptionData;
use App\Models\Timetable\TimetableScheduleException;
use App\Repositories\Contracts\Timetable\TimetableScheduleExceptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetableScheduleExceptionRepository implements TimetableScheduleExceptionRepositoryInterface
{
    protected array $with = [
        'academicYear',
        'schoolClass',
        'section',
    ];

    public function all(array $filters = []): Collection
    {
        return TimetableScheduleException::query()
            ->with($this->with)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($exceptionQuery) use ($search): void {
                    $exceptionQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('exception_type', 'like', "%{$search}%")
                        ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('section', fn ($sectionQuery) => $sectionQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($filters['school_class_id'] ?? null, fn ($query, $value) => $query->where('school_class_id', $value))
            ->when($filters['section_id'] ?? null, fn ($query, $value) => $query->where('section_id', $value))
            ->when($filters['exception_type'] ?? null, fn ($query, $value) => $query->where('exception_type', $value))
            ->when($filters['exception_date'] ?? null, fn ($query, $value) => $query->whereDate('exception_date', $value))
            ->orderByDesc('exception_date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(TimetableScheduleExceptionData $data): TimetableScheduleException
    {
        /** @var TimetableScheduleException $exception */
        $exception = TimetableScheduleException::create($data->attributes);

        return $exception->load($this->with);
    }

    public function update(TimetableScheduleException $exception, TimetableScheduleExceptionData $data): TimetableScheduleException
    {
        $exception->update($data->attributes);

        return $exception->refresh()->load($this->with);
    }

    public function delete(TimetableScheduleException $exception): void
    {
        $exception->delete();
    }
}
