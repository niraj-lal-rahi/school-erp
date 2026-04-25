<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetableScheduleExceptionData;
use App\Models\Timetable\TimetableScheduleException;
use Illuminate\Database\Eloquent\Collection;

interface TimetableScheduleExceptionRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetableScheduleExceptionData $data): TimetableScheduleException;

    public function update(TimetableScheduleException $exception, TimetableScheduleExceptionData $data): TimetableScheduleException;

    public function delete(TimetableScheduleException $exception): void;
}
