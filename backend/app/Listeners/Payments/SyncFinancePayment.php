<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentRefunded;
use App\Events\Payments\PaymentSuccessful;
use App\Services\Payments\FinancePaymentSyncService;

class SyncFinancePayment
{
    public function __construct(
        protected FinancePaymentSyncService $financeSync,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof PaymentSuccessful && $event->transaction->payable_type === 'school_fee') {
            $this->financeSync->syncSuccessfulPayment($event->transaction);

            return;
        }

        if ($event instanceof PaymentRefunded && $event->transaction->payable_type === 'school_fee') {
            $this->financeSync->syncRefund($event->transaction, $event->refund);
        }
    }
}
