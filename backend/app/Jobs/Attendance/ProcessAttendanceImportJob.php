<?php

namespace App\Jobs\Attendance;

use App\Models\Attendance\AttendanceImport;
use App\Services\Attendance\AttendanceImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceImportJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $attendanceImportId,
    ) {
    }

    public function handle(AttendanceImportService $service): void
    {
        $attendanceImport = AttendanceImport::withoutGlobalScopes()->find($this->attendanceImportId);

        if (! $attendanceImport) {
            return;
        }

        $service->process($attendanceImport);
    }
}
