<?php

namespace App\Events\Reports;

use App\Models\Reports\ReportRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportRunFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ReportRun $reportRun,
        public string $errorMessage,
        public array $context = [],
    ) {
    }
}
