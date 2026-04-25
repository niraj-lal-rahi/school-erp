<?php

namespace App\Events\Timetable;

use App\Models\Timetable\TimetableVersion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimetableVersionPublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public TimetableVersion $version,
        public int $performedBy,
        public ?string $remarks = null,
    ) {
    }
}
