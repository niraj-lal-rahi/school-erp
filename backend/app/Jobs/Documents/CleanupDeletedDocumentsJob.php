<?php

namespace App\Jobs\Documents;

use App\Jobs\Storage\FileCleanupJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupDeletedDocumentsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $olderThanDays = 30,
    ) {
    }

    public function handle(): void
    {
        FileCleanupJob::dispatch($this->olderThanDays);
    }
}
