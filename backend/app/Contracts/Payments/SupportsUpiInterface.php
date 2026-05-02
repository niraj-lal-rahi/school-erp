<?php

namespace App\Contracts\Payments;

use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;

interface SupportsUpiInterface
{
    public function createUpiQrPayload(PaymentTransaction $transaction, array $payload = []): string;

    public function createUpiPaymentRequest(PaymentTransaction $transaction, array $payload = []): array;

    public function verifyUpiReference(PaymentTransaction $transaction, string $referenceNumber, array $payload = []): array;

    public function expireUpiRequest(UpiPaymentRequest $upiPaymentRequest): array;
}
