<?php

namespace App\Listeners\Attendance;

use App\Events\Attendance\StudentAttendanceSessionSubmitted;
use Illuminate\Support\Facades\Log;

class LogStudentAttendanceSessionSubmitted
{
    public function handle(StudentAttendanceSessionSubmitted $event): void
    {
        Log::info('Student attendance session submitted.', [
            'session_id' => $event->session->id,
            'school_id' => $event->session->school_id,
            'attendance_date' => optional($event->session->attendance_date)?->toDateString(),
        ]);
    }
}
