<?php

namespace App\Events\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentVerification;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentVerified
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentVerification $verification,
        public ?User $actor = null,
        public array $metadata = [],
    ) {
    }
}
