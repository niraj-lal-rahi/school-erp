<?php

namespace App\Listeners\Attendance;

use App\Events\Attendance\BiometricLogsQueued;
use Illuminate\Support\Facades\Log;

class LogBiometricLogsQueued
{
    public function handle(BiometricLogsQueued $event): void
    {
        Log::info('Biometric log processing queued.', [
            'school_id' => $event->schoolId,
            'filters' => $event->filters,
        ]);
    }
}
