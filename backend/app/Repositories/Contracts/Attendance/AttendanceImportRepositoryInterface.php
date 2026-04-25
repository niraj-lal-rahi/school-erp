<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendanceImportData;
use App\Models\Attendance\AttendanceImport;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceImportRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(AttendanceImportData $data): AttendanceImport;

    public function update(AttendanceImport $attendanceImport, AttendanceImportData $data): AttendanceImport;
}
