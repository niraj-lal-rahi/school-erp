<?php

namespace App\Jobs\Reports;

use App\Services\Reports\ReportExecutionService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class RunReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 900;

    public function __construct(
        public int $reportDefinitionId,
        public array $parameters = [],
        public string $fileType = 'json',
        public string $runType = 'manual',
        public ?int $initiatedBy = null,
        public array $context = [],
    ) {
        $this->onQueue(config('queue.routing.reports', QueueNames::REPORTS));
    }

    public function backoff(): array
    {
        return JobRetryProfile::reports();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('report-run:'.$this->reportDefinitionId.':'.md5(json_encode([
                'parameters' => $this->parameters,
                'file_type' => $this->fileType,
                'run_type' => $this->runType,
                'context' => $this->context,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))))
                ->releaseAfter(60)
                ->expireAfter(1800),
        ];
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
