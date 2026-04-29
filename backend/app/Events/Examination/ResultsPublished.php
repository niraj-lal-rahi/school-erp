<?php

namespace App\Events\Examination;

use App\Models\Examination\ResultPublication;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResultsPublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ResultPublication $publication,
        public array $options = [],
    ) {
    }
}
