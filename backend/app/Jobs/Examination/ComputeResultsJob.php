<?php

namespace App\Jobs\Examination;

use App\Models\Examination\Exam;
use App\Services\Examination\ResultComputationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeResultsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $examId,
        public ?int $gradingSystemId = null,
    ) {
    }

    public function handle(ResultComputationService $service): void
    {
        $exam = Exam::query()->findOrFail($this->examId);

        $service->computeForExam($exam, $this->gradingSystemId);
    }
}
