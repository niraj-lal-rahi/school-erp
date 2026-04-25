<?php

namespace App\Events\Attendance;

use App\Models\Attendance\StudentAttendanceSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAttendanceSessionLocked
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public StudentAttendanceSession $session,
    ) {
    }
}
