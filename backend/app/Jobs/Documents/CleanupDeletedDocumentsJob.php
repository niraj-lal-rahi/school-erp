<?php

namespace App\Jobs\Documents;

use App\Models\Documents\DocumentFile;
use App\Services\Documents\DocumentStorageService;
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

    public function handle(DocumentStorageService $storage): void
    {
        DocumentFile::withTrashed()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '<=', now()->subDays($this->olderThanDays))
            ->chunkById(100, function ($files) use ($storage): void {
                foreach ($files as $file) {
                    if ($storage->exists($file)) {
                        $storage->delete($file);
                    }
                }
            });
    }
}
