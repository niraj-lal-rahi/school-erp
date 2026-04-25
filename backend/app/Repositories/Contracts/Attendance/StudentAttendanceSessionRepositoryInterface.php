<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceSessionData;
use App\Models\Attendance\StudentAttendanceSession;
use Illuminate\Database\Eloquent\Collection;

interface StudentAttendanceSessionRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(StudentAttendanceSessionData $data): StudentAttendanceSession;

    public function update(StudentAttendanceSession $session, StudentAttendanceSessionData $data): StudentAttendanceSession;

    public function delete(StudentAttendanceSession $session): void;

    public function refreshWithRelations(StudentAttendanceSession $session): StudentAttendanceSession;
}
