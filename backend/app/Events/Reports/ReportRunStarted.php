<?php

namespace App\Events\Reports;

use App\Models\Reports\ReportRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportRunStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ReportRun $reportRun,
        public array $context = [],
    ) {
    }
}
