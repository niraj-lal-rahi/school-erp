<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\SupportsUpiInterface;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use Illuminate\Support\Facades\URL;

class ManualUpiPaymentGateway extends AbstractPaymentGateway implements SupportsUpiInterface
{
    public function initiatePayment(PaymentTransaction $transaction, array $payload = []): array
    {
        return [
            'provider' => 'upi_manual',
            'supported_methods' => $this->getSupportedMethods(),
            'upi_request' => $this->createUpiPaymentRequest($transaction, $payload),
        ];
    }

    public function verifyPayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $referenceNumber = (string) ($payload['upi_reference_no'] ?? '');

        return [
            'verified' => $referenceNumber !== '',
            'status' => $referenceNumber !== '' ? 'manually_verified' : 'failed',
            'verification_status' => $referenceNumber !== '' ? 'manual_review' : 'failed',
            'upi_reference_no' => $referenceNumber ?: null,
            'metadata' => ['provider_payload' => $payload],
        ];
    }

    public function createUpiQrPayload(PaymentTransaction $transaction, array $payload = []): string
    {
        $upiId = $payload['upi_vpa'] ?? $transaction->upi_vpa ?? $this->config('upi_vpa');
        $payee = rawurlencode($payload['payee_name'] ?? $this->gateway->name);
        $amount = number_format((float) $transaction->amount, 2, '.', '');

        return "upi://pay?pa={$upiId}&pn={$payee}&am={$amount}&cu={$transaction->currency}&tr={$transaction->transaction_no}";
    }

    public function createUpiPaymentRequest(PaymentTransaction $transaction, array $payload = []): array
    {
        $qrPayload = $this->createUpiQrPayload($transaction, $payload);

        return [
            'upi_vpa' => $payload['upi_vpa'] ?? $transaction->upi_vpa ?? $this->config('upi_vpa'),
            'payee_name' => $payload['payee_name'] ?? $this->gateway->name,
            'qr_payload' => $qrPayload,
            'intent_url' => URL::to('/').'?upi_intent='.rawurlencode($qrPayload),
            'status' => 'pending',
        ];
    }

    public function verifyUpiReference(PaymentTransaction $transaction, string $referenceNumber, array $payload = []): array
    {
        return [
            'verified' => $referenceNumber !== '',
            'status' => $referenceNumber !== '' ? 'manually_verified' : 'failed',
            'verification_status' => $referenceNumber !== '' ? 'manual_review' : 'failed',
            'upi_reference_no' => $referenceNumber,
            'metadata' => ['provider_payload' => $payload],
        ];
    }

    public function expireUpiRequest(UpiPaymentRequest $upiPaymentRequest): array
    {
        return [
            'status' => 'expired',
            'expires_at' => $upiPaymentRequest->expires_at,
        ];
    }

    public function getSupportedMethods(): array
    {
        return ['upi'];
    }
}
