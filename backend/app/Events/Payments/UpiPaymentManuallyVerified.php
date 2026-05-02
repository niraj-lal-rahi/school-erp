<?php

namespace App\Events\Payments;

use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpiPaymentManuallyVerified
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public UpiPaymentRequest $upiPaymentRequest,
        public PaymentTransaction $transaction,
        public ?User $verifiedBy = null,
    ) {
    }
}
