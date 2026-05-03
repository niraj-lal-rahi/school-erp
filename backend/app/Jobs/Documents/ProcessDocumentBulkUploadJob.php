<?php

namespace App\Jobs\Documents;

use App\Services\Documents\DocumentBulkUploadService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentBulkUploadJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 900;

    public function __construct(
        public int $bulkUploadId,
    ) {
        $this->onQueue(config('queue.routing.documents', QueueNames::DOCUMENTS));
    }

    public function backoff(): array
    {
        return JobRetryProfile::documents();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('document-bulk-upload:'.$this->bulkUploadId))
                ->releaseAfter(30)
                ->expireAfter(1800),
        ];
    }

    public function handle(DocumentBulkUploadService $bulkUploads): void
    {
        $bulkUpload = $bulkUploads->findOrFail($this->bulkUploadId);
        $bulkUploads->markProcessing($bulkUpload);
        $bulkUploads->markCompleted($bulkUpload, [
            'total_files' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'error_log' => 'Bulk upload processing placeholder executed. Row-level parsing will be implemented in the queued importer pass.',
        ]);
    }
}
