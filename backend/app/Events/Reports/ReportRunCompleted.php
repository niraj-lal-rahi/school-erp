<?php

namespace App\Events\Reports;

use App\Models\Reports\ReportRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportRunCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ReportRun $reportRun,
        public array $data = [],
        public array $context = [],
    ) {
    }
}
