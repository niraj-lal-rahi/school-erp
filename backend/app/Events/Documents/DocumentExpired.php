<?php

namespace App\Events\Documents;

use App\Models\Documents\Document;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentExpired
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Document $document,
        public array $metadata = [],
    ) {
    }
}
