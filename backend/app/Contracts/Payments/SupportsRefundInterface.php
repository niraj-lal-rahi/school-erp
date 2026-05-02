<?php

namespace App\Contracts\Payments;

use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;

interface SupportsRefundInterface
{
    public function refund(PaymentTransaction $transaction, array $payload = []): array;

    public function mapRefundResponse(PaymentRefund $refund, array $response): array;
}
