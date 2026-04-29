<?php

namespace App\Jobs\Reports;

use App\Services\Reports\ReportExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $reportDefinitionId,
        public array $parameters = [],
        public string $fileType = 'json',
        public string $runType = 'manual',
        public ?int $initiatedBy = null,
        public array $context = [],
    ) {
    }

    public function handle(ReportExecutionService $reports): void
    {
        $reports->runReport(
            ['report_definition_id' => $this->reportDefinitionId],
            $this->parameters,
            $this->fileType,
            $this->runType,
            $this->initiatedBy,
            $this->context,
        );
    }
}
