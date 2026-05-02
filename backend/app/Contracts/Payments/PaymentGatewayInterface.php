<?php

namespace App\Contracts\Payments;

use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function setGateway(PaymentGateway $gateway): static;

    public function createOrder(array $payload): array;

    public function initiatePayment(PaymentTransaction $transaction, array $payload = []): array;

    public function verifyPayment(PaymentTransaction $transaction, array $payload = []): array;

    public function refund(PaymentTransaction $transaction, array $payload = []): array;

    public function getSupportedMethods(): array;
}
