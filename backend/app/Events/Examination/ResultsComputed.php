<?php

namespace App\Events\Examination;

use App\Models\Examination\Exam;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultsComputed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Exam $exam,
        public ?int $gradingSystemId = null,
    ) {
    }
}
