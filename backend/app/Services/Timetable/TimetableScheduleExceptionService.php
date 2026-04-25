<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetableScheduleExceptionData;
use App\Models\Section;
use App\Models\Timetable\TimetableScheduleException;
use App\Repositories\Contracts\Timetable\TimetableScheduleExceptionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableScheduleExceptionService
{
    public function __construct(
        protected TimetableScheduleExceptionRepositoryInterface $exceptions,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->exceptions->all($filters);
    }

    public function create(TimetableScheduleExceptionData $data): TimetableScheduleException
    {
        return DB::transaction(function () use ($data): TimetableScheduleException {
            $this->validateSectionClassConsistency($data->attributes);

            return $this->exceptions->create($data);
        });
    }

    public function update(TimetableScheduleException $exception, TimetableScheduleExceptionData $data): TimetableScheduleException
    {
        return DB::transaction(function () use ($exception, $data): TimetableScheduleException {
            $this->validateSectionClassConsistency($data->attributes);

            return $this->exceptions->update($exception, $data);
        });
    }

    public function delete(TimetableScheduleException $exception): void
    {
        DB::transaction(fn () => $this->exceptions->delete($exception));
    }

    protected function validateSectionClassConsistency(array $attributes): void
    {
        if (! empty($attributes['section_id']) && empty($attributes['school_class_id'])) {
            throw ValidationException::withMessages([
                'school_class_id' => ['A class must be selected when a section is provided.'],
            ]);
        }

        if (! empty($attributes['section_id']) && ! empty($attributes['school_class_id'])) {
            $section = Section::query()->findOrFail($attributes['section_id']);

            if ((int) $section->school_class_id !== (int) $attributes['school_class_id']) {
                throw ValidationException::withMessages([
                    'section_id' => ['The selected section does not belong to the selected class.'],
                ]);
            }
        }
    }
}
