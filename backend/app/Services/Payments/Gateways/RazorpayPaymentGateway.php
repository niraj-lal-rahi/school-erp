<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\SupportsRefundInterface;
use App\Contracts\Payments\SupportsUpiInterface;
use App\Contracts\Payments\SupportsWebhookInterface;
use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;
use App\Models\Payments\UpiPaymentRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

class RazorpayPaymentGateway extends AbstractPaymentGateway implements SupportsRefundInterface, SupportsWebhookInterface, SupportsUpiInterface
{
    public function initiatePayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $order = $this->createOrder([
            'amount' => (float) $transaction->amount,
            'currency' => $transaction->currency,
            'metadata' => array_merge($transaction->metadata ?? [], $payload),
        ]);

        return [
            'provider' => 'razorpay',
            'order' => $order,
            'gateway_order_id' => $order['order_id'],
            'supported_methods' => $this->getSupportedMethods(),
            'checkout' => [
                'key' => $this->config('key_id'),
                'currency' => $transaction->currency,
                'amount' => (float) $transaction->amount,
                'order_id' => $order['order_id'],
                'name' => $this->gateway->name,
            ],
        ];
    }

    public function verifyPayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $expected = hash_hmac(
            'sha256',
            ($payload['gateway_order_id'] ?? '').'|'.($payload['gateway_payment_id'] ?? ''),
            (string) $this->config('key_secret', '')
        );

        $provided = (string) ($payload['gateway_signature'] ?? '');
        $verified = $provided !== '' && hash_equals($expected, $provided);

        return [
            'verified' => $verified,
            'status' => $verified ? 'successful' : 'failed',
            'verification_status' => $verified ? 'verified' : 'failed',
            'gateway_order_id' => $payload['gateway_order_id'] ?? $transaction->gateway_order_id,
            'gateway_payment_id' => $payload['gateway_payment_id'] ?? null,
            'gateway_signature' => $provided ?: null,
            'metadata' => ['provider_payload' => $payload],
        ];
    }

    public function processWebhook(array $payload, ?string $signature = null): array
    {
        return [
            'provider' => 'razorpay',
            'event_id' => Arr::get($payload, 'payload.payment.entity.id') ?? Arr::get($payload, 'account_id'),
            'event_type' => Arr::get($payload, 'event', 'unknown'),
            'signature' => $signature,
            'is_valid' => true,
            'payload' => $payload,
        ];
    }

    public function refund(PaymentTransaction $transaction, array $payload = []): array
    {
        return [
            'status' => 'processing',
            'gateway_refund_id' => 'rfnd_'.$transaction->id.'_'.now()->timestamp,
            'amount' => (float) ($payload['amount'] ?? $transaction->amount),
            'metadata' => $payload,
        ];
    }

    public function mapRefundResponse(PaymentRefund $refund, array $response): array
    {
        return [
            'gateway_refund_id' => $response['gateway_refund_id'] ?? $refund->gateway_refund_id,
            'status' => ($response['status'] ?? 'processing') === 'failed' ? 'failed' : 'processing',
            'metadata' => array_merge($refund->metadata ?? [], ['gateway_response' => $response]),
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
        return ['upi', 'card', 'netbanking', 'wallet'];
    }
}
