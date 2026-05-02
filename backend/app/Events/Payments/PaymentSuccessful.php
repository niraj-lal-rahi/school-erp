<?php

namespace App\Events\Payments;

use App\Models\Payments\PaymentTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public PaymentTransaction $transaction,
    ) {
    }
}
