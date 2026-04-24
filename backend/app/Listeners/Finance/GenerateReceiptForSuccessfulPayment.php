<?php

namespace App\Listeners\Finance;

use App\Events\Finance\PaymentSuccessful;
use App\Services\Finance\ReceiptService;

class GenerateReceiptForSuccessfulPayment
{
    public function __construct(
        protected ReceiptService $receipts,
    ) {
    }

    public function handle(PaymentSuccessful $event): void
    {
        $this->receipts->generateForPayment($event->payment);
    }
}
