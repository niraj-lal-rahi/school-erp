<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payments\PaymentTransaction;

class OfflinePaymentGateway extends AbstractPaymentGateway
{
    public function initiatePayment(PaymentTransaction $transaction, array $payload = []): array
    {
        return [
            'provider' => 'offline',
            'status' => 'pending',
            'message' => 'Offline payment has been recorded and is awaiting manual confirmation.',
            'supported_methods' => $this->getSupportedMethods(),
            'metadata' => $payload,
        ];
    }

    public function verifyPayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $verified = ($payload['status'] ?? null) === 'successful';

        return [
            'verified' => $verified,
            'status' => $verified ? 'successful' : 'failed',
            'verification_status' => $verified ? 'verified' : 'failed',
            'metadata' => ['provider_payload' => $payload],
        ];
    }

    public function getSupportedMethods(): array
    {
        return ['cash', 'bank_transfer', 'cheque', 'other'];
    }
}
