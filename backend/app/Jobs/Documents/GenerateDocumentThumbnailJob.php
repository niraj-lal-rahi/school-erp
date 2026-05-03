<?php

namespace App\Jobs\Documents;

use App\Models\Documents\DocumentFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDocumentThumbnailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $documentFileId,
    ) {
    }

    public function handle(): void
    {
        DocumentFile::query()->find($this->documentFileId);
        // Placeholder for future thumbnail generation pipeline.
    }
}
