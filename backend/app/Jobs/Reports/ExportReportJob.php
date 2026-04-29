<?php

namespace App\Jobs\Reports;

use App\Services\Reports\ReportExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $reportRunId,
        public ?string $fileType = null,
        public array $context = [],
    ) {
    }

    public function handle(ReportExecutionService $reports): void
    {
        $reportRun = $reports->findRunOrFail($this->reportRunId);

        if ($this->fileType && $this->fileType !== $reportRun->file_type) {
            $reportRun = $reportRun->fresh();
            $reportRun->update(['file_type' => $this->fileType]);
            $reportRun = $reportRun->fresh();
        }

        if ($reportRun->status === 'completed' && $reportRun->file_path && ! $this->fileType) {
            return;
        }

        $reports->processRun($reportRun, $this->context);
    }
}
