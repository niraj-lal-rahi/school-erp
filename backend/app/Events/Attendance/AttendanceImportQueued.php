<?php

namespace App\Events\Attendance;

use App\Models\Attendance\AttendanceImport;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceImportQueued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AttendanceImport $attendanceImport,
    ) {
    }
}
