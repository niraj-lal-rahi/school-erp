<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\SupportsRefundInterface;
use App\Contracts\Payments\SupportsWebhookInterface;
use App\Models\Payments\PaymentRefund;
use App\Models\Payments\PaymentTransaction;

class StripePaymentGateway extends AbstractPaymentGateway implements SupportsRefundInterface, SupportsWebhookInterface
{
    public function initiatePayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $order = $this->createOrder([
            'amount' => (float) $transaction->amount,
            'currency' => $transaction->currency,
            'metadata' => array_merge($transaction->metadata ?? [], $payload),
        ]);

        return [
            'provider' => 'stripe',
            'payment_intent_id' => 'pi_'.$order['order_id'],
            'client_secret' => 'pi_'.$order['order_id'].'_secret',
            'gateway_order_id' => $order['order_id'],
            'supported_methods' => $this->getSupportedMethods(),
        ];
    }

    public function verifyPayment(PaymentTransaction $transaction, array $payload = []): array
    {
        $paymentIntentId = (string) ($payload['gateway_payment_id'] ?? '');
        $verified = str_starts_with($paymentIntentId, 'pi_');

        return [
            'verified' => $verified,
            'status' => $verified ? 'successful' : 'failed',
            'verification_status' => $verified ? 'verified' : 'failed',
            'gateway_payment_id' => $paymentIntentId ?: null,
            'metadata' => ['provider_payload' => $payload],
        ];
    }

    public function processWebhook(array $payload, ?string $signature = null): array
    {
        $secret = (string) $this->config('webhook_secret', '');
        $expected = $secret !== ''
            ? hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $secret)
            : null;
        $isValid = $signature !== null && $signature !== '' && $expected !== null
            ? hash_equals($expected, $signature)
            : false;

        return [
            'provider' => 'stripe',
            'event_id' => $payload['id'] ?? null,
            'event_type' => $payload['type'] ?? 'unknown',
            'signature' => $signature,
            'is_valid' => $isValid,
            'payload' => $payload,
        ];
    }

    public function refund(PaymentTransaction $transaction, array $payload = []): array
    {
        return [
            'status' => 'processing',
            'gateway_refund_id' => 're_'.$transaction->id.'_'.now()->timestamp,
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

    public function getSupportedMethods(): array
    {
        return ['card', 'wallet'];
    }
}
