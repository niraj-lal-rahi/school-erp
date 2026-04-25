<?php

namespace App\Events\Attendance;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BiometricLogsQueued
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public int $schoolId,
        public array $filters = [],
    ) {
    }
}
