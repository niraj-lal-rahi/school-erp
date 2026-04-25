<?php

namespace App\Listeners\Timetable;

use App\Events\Timetable\TimetableVersionPublished;
use Illuminate\Support\Facades\Log;

class LogTimetableVersionPublished
{
    public function handle(TimetableVersionPublished $event): void
    {
        Log::info('Timetable version published.', [
            'version_id' => $event->version->id,
            'school_id' => $event->version->school_id,
            'academic_year_id' => $event->version->academic_year_id,
            'performed_by' => $event->performedBy,
            'remarks' => $event->remarks,
        ]);
    }
}
