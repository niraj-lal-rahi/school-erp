<?php

namespace App\Events\Communication;

use App\Models\Communication\CommunicationMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CommunicationMessage $message,
    ) {
    }
}
