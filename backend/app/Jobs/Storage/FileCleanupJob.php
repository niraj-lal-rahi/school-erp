<?php

namespace App\Jobs\Storage;

use App\Models\Documents\DocumentFile;
use App\Services\Security\FileSecurityService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class FileCleanupJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public int $olderThanDays = 30,
    ) {
        $this->onQueue(config('queue.routing.documents', QueueNames::DOCUMENTS));
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('file-cleanup-'.$this->olderThanDays))->expireAfter(600),
        ];
    }

    public function backoff(): array
    {
        return JobRetryProfile::documents()['backoff'] ?? [60, 300, 900];
    }

    public function handle(FileSecurityService $security): void
    {
        DocumentFile::withTrashed()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', now()->subDays($this->olderThanDays))
            ->chunkById(100, function ($files) use ($security): void {
                foreach ($files as $file) {
                    $security->deletePhysicalFile($file);
                }
            });
    }
}
