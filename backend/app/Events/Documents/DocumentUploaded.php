<?php

namespace App\Events\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentFile;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class DocumentUploaded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentFile $file,
        public ?User $actor = null,
        public array $metadata = [],
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {
    }

    public static function fromRequest(Document $document, DocumentFile $file, ?User $actor = null, array $metadata = [], ?Request $request = null): self
    {
        return new self($document, $file, $actor, $metadata, $request?->ip(), $request?->userAgent());
    }
}
