<?php

namespace App\Listeners\Attendance;

use App\Events\Attendance\StudentAttendanceSessionLocked;
use Illuminate\Support\Facades\Log;

class LogStudentAttendanceSessionLocked
{
    public function handle(StudentAttendanceSessionLocked $event): void
    {
        Log::info('Student attendance session locked.', [
            'session_id' => $event->session->id,
            'school_id' => $event->session->school_id,
            'attendance_date' => optional($event->session->attendance_date)?->toDateString(),
        ]);
    }
}
