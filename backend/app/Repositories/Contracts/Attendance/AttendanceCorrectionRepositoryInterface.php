<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendanceCorrectionData;
use App\Models\Attendance\AttendanceCorrection;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceCorrectionRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(AttendanceCorrectionData $data): AttendanceCorrection;

    public function update(AttendanceCorrection $correction, AttendanceCorrectionData $data): AttendanceCorrection;
}
