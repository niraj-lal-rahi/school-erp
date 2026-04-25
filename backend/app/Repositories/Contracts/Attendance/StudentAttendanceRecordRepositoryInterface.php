<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceRecordData;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\Attendance\StudentAttendanceSession;
use Illuminate\Database\Eloquent\Collection;

interface StudentAttendanceRecordRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(StudentAttendanceRecordData $data): StudentAttendanceRecord;

    public function upsertForSession(StudentAttendanceSession $session, array $records, int $schoolId, int $markedBy): void;

    public function update(StudentAttendanceRecord $record, StudentAttendanceRecordData $data): StudentAttendanceRecord;

    public function delete(StudentAttendanceRecord $record): void;
}
