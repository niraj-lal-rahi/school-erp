<?php

namespace App\Events\Payments;

use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpiPaymentPendingVerification
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public UpiPaymentRequest $upiPaymentRequest,
        public PaymentTransaction $transaction,
    ) {
    }
}
