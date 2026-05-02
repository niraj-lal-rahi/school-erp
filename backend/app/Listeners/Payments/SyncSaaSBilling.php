<?php

namespace App\Listeners\Payments;

use App\Events\Payments\PaymentRefunded;
use App\Events\Payments\PaymentSuccessful;
use App\Services\Payments\SaaSBillingSyncService;

class SyncSaaSBilling
{
    public function __construct(
        protected SaaSBillingSyncService $billingSync,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof PaymentSuccessful && $event->transaction->payable_type === 'saas_subscription') {
            $this->billingSync->syncSuccessfulPayment($event->transaction);

            return;
        }

        if ($event instanceof PaymentRefunded && $event->transaction->payable_type === 'saas_subscription') {
            $this->billingSync->syncRefund($event->transaction, $event->refund);
        }
    }
}
