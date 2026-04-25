<?php

namespace App\Jobs\Attendance;

use App\Services\Attendance\BiometricLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBiometricLogsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $schoolId,
        public array $filters = [],
    ) {
    }

    public function handle(BiometricLogService $service): void
    {
        $service->processPending($this->schoolId, $this->filters);
    }
}
