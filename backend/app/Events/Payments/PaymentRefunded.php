<?php

namespace App\Events\Payments;

use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentRefunded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaymentRefund $refund,
        public PaymentTransaction $transaction,
    ) {
    }
}
