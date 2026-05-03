<?php

namespace App\Jobs\Examination;

use App\Models\Examination\Exam;
use App\Services\Examination\ResultComputationService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ComputeResultsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 1200;

    public function __construct(
        public int $examId,
        public ?int $gradingSystemId = null,
    ) {
        $this->onQueue(config('queue.routing.reports', QueueNames::REPORTS));
    }

    public function backoff(): array
    {
        return JobRetryProfile::computation();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('exam-compute-results:'.$this->examId.':'.($this->gradingSystemId ?? 'default')))
                ->releaseAfter(60)
                ->expireAfter(1800),
        ];
    }

    public function handle(ResultComputationService $service): void
    {
        $exam = Exam::query()->findOrFail($this->examId);

        $service->computeForExam($exam, $this->gradingSystemId);
    }
}
