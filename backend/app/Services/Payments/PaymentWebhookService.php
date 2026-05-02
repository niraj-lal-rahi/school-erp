<?php

namespace App\Services\Payments;

use App\Contracts\Payments\SupportsWebhookInterface;
use App\Jobs\Payments\ProcessPaymentWebhookJob;
use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentWebhookEvent;
use App\Repositories\Contracts\Payments\PaymentWebhookRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use RuntimeException;

class PaymentWebhookService
{
    public function __construct(
        protected PaymentWebhookRepositoryInterface $webhooks,
        protected PaymentGatewayService $gateways,
        protected PaymentTransactionService $transactions,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->webhooks->paginate($filters, $perPage);
    }

    public function receiveWebhook(string $provider, array $payload, ?string $signature = null, ?int $schoolId = null): PaymentWebhookEvent
    {
        $eventId = $this->extractEventId($payload);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $existing = $this->webhooks->findDuplicate($provider, $eventId, $payloadJson, $signature);

        if ($existing) {
            return $existing;
        }

        $event = $this->webhooks->create([
            'school_id' => $schoolId,
            'provider' => $provider,
            'event_type' => $this->extractEventType($payload),
            'event_id' => $eventId,
            'payload' => $payloadJson,
            'signature' => $signature,
            'processed' => false,
        ]);

        DB::afterCommit(fn () => ProcessPaymentWebhookJob::dispatch($event->id));

        return $event;
    }

    public function processWebhook(PaymentWebhookEvent $event): PaymentWebhookEvent
    {
        if ($event->processed) {
            return $event;
        }

        return DB::transaction(function () use ($event): PaymentWebhookEvent {
            $payload = json_decode($event->payload, true) ?: [];
            $gateway = $this->resolveGatewayForWebhook($event->provider, $event->school_id);
            $implementation = $this->gateways->resolveGatewayImplementation($gateway);

            if (! $implementation instanceof SupportsWebhookInterface) {
                return $this->webhooks->update($event, [
                    'processed' => true,
                    'processed_at' => now(),
                    'error_message' => 'Gateway does not support webhook processing.',
                ]);
            }

            $result = $implementation->processWebhook($payload, $event->signature);
            $transaction = null;
            $gatewayOrderId = $this->extractGatewayOrderId($payload);
            $gatewayPaymentId = $this->extractGatewayPaymentId($payload);

            if ($gatewayOrderId !== null) {
                $candidate = $this->transactions->findByGatewayOrderId(
                    $gatewayOrderId,
                    $event->school_id
                );
                $transaction = $candidate ? $this->transactions->findOrFail($candidate->id) : null;
            } elseif ($gatewayPaymentId !== null) {
                $candidate = $this->transactions->findByGatewayPaymentId($gatewayPaymentId, $event->school_id);
                $transaction = $candidate ? $this->transactions->findOrFail($candidate->id) : null;
            }

            if ($transaction) {
                $this->transactions->verifyPayment($transaction, [
                    'gateway_order_id' => $gatewayOrderId ?? $transaction->gateway_order_id,
                    'gateway_payment_id' => $gatewayPaymentId ?? $transaction->gateway_payment_id,
                    'gateway_signature' => $event->signature,
                    'metadata' => ['webhook' => $result],
                ]);
            }

            return $this->webhooks->update($event, [
                'processed' => true,
                'processed_at' => now(),
                'error_message' => null,
            ]);
        });
    }

    protected function resolveGatewayForWebhook(string $provider, ?int $schoolId = null): PaymentGateway
    {
        $gateway = $this->gateways->getActiveGatewayForTenant($provider, $schoolId)
            ?? $this->gateways->listActiveForTenant($schoolId)->firstWhere('provider', $provider);

        if (! $gateway) {
            throw new RuntimeException(sprintf('No active %s gateway is configured for webhook processing.', $provider));
        }

        return $gateway;
    }

    protected function extractEventId(array $payload): ?string
    {
        return Arr::first([
            Arr::get($payload, 'id'),
            Arr::get($payload, 'event_id'),
            Arr::get($payload, 'payload.id'),
            Arr::get($payload, 'payload.event_id'),
            Arr::get($payload, 'data.id'),
            Arr::get($payload, 'data.event_id'),
        ], fn ($value) => is_string($value) && $value !== '');
    }

    protected function extractEventType(array $payload): string
    {
        return Arr::first([
            Arr::get($payload, 'event'),
            Arr::get($payload, 'type'),
            Arr::get($payload, 'payload.event'),
            Arr::get($payload, 'payload.type'),
            Arr::get($payload, 'data.type'),
        ], fn ($value) => is_string($value) && $value !== '') ?? 'unknown';
    }

    protected function extractGatewayOrderId(array $payload): ?string
    {
        return Arr::first([
            Arr::get($payload, 'payload.payment.entity.order_id'),
            Arr::get($payload, 'payment.entity.order_id'),
            Arr::get($payload, 'data.object.order_id'),
        ], fn ($value) => is_string($value) && $value !== '');
    }

    protected function extractGatewayPaymentId(array $payload): ?string
    {
        return Arr::first([
            Arr::get($payload, 'payload.payment.entity.id'),
            Arr::get($payload, 'payment.entity.id'),
            Arr::get($payload, 'data.object.id'),
        ], fn ($value) => is_string($value) && $value !== '');
    }
}
