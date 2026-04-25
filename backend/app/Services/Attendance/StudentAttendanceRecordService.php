<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceRecordData;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Repositories\Contracts\Attendance\StudentAttendanceRecordRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceRecordService
{
    public function __construct(
        protected StudentAttendanceRecordRepositoryInterface $records,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->records->all($filters);
    }

    public function update(StudentAttendanceRecord $record, StudentAttendanceRecordData $data): StudentAttendanceRecord
    {
        if ($record->session && $record->session->status === 'locked') {
            throw ValidationException::withMessages([
                'record' => ['Attendance records in a locked session cannot be changed.'],
            ]);
        }

        return DB::transaction(fn (): StudentAttendanceRecord => $this->records->update($record, $data));
    }

    public function delete(StudentAttendanceRecord $record): void
    {
        if ($record->session && $record->session->status === 'locked') {
            throw ValidationException::withMessages([
                'record' => ['Attendance records in a locked session cannot be deleted.'],
            ]);
        }

        DB::transaction(function () use ($record): void {
            $this->records->delete($record);
        });
    }
}
