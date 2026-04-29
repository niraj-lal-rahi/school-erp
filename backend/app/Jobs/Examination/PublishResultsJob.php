<?php

namespace App\Jobs\Examination;

use App\Models\Examination\Exam;
use App\Services\Examination\ResultPublicationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishResultsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $examId,
        public ?int $publishedBy = null,
        public array $options = [],
    ) {
    }

    public function handle(ResultPublicationService $service): void
    {
        $exam = Exam::query()->findOrFail($this->examId);

        $service->publish($exam, $this->publishedBy, $this->options);
    }
}
