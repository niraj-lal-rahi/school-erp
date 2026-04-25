<?php

namespace App\Listeners\Attendance;

use App\Events\Attendance\AttendanceImportQueued;
use Illuminate\Support\Facades\Log;

class LogAttendanceImportQueued
{
    public function handle(AttendanceImportQueued $event): void
    {
        Log::info('Attendance import queued.', [
            'attendance_import_id' => $event->attendanceImport->id,
            'school_id' => $event->attendanceImport->school_id,
            'import_type' => $event->attendanceImport->import_type,
        ]);
    }
}
