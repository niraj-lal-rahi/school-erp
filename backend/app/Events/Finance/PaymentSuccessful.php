<?php

namespace App\Events\Finance;

use App\Models\Finance\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {
    }
}
