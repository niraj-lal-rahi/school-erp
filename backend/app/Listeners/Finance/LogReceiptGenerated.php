<?php

namespace App\Listeners\Finance;

use App\Events\Finance\ReceiptGenerated;
use Illuminate\Support\Facades\Log;

class LogReceiptGenerated
{
    public function handle(ReceiptGenerated $event): void
    {
        Log::info('Finance receipt generated.', [
            'receipt_id' => $event->receipt->id,
            'receipt_no' => $event->receipt->receipt_no,
            'payment_id' => $event->receipt->payment_id,
        ]);
    }
}
