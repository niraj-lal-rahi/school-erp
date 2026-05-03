<?php

namespace App\Jobs\Documents;

use App\Services\Documents\DocumentExpiryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiredDocumentsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(DocumentExpiryService $expiryService): void
    {
        $expiryService->markExpired();
        $expiryService->sendExpiryReminders();
    }
}
