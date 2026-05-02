<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentSuccessful;
use App\Jobs\Payments\GeneratePaymentReceiptJob;

class GenerateReceipt
{
    public function handle(PaymentSuccessful $event): void
    {
        if ($event->transaction->payable_type !== 'school_fee') {
            return;
        }

        GeneratePaymentReceiptJob::dispatch($event->transaction->id);
    }
}
