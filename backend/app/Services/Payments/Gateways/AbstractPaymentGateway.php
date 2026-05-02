<?php

namespace App\Services\Payments\Gateways;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Models\Payments\PaymentGateway;
use App\Models\Payments\PaymentTransaction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;

    public function setGateway(PaymentGateway $gateway): static
    {
        $this->gateway = $gateway->loadMissing('credentials');

        return $this;
    }

    public function createOrder(array $payload): array
    {
        return [
            'order_id' => strtoupper(Str::random(20)),
            'amount' => (float) Arr::get($payload, 'amount', 0),
            'currency' => Arr::get($payload, 'currency', 'INR'),
            'status' => 'created',
            'metadata' => Arr::get($payload, 'metadata', []),
        ];
    }

    public function refund(PaymentTransaction $transaction, array $payload = []): array
    {
        return [
            'status' => 'failed',
            'message' => 'Refund is not supported by this gateway.',
            'transaction_id' => $transaction->id,
            'metadata' => $payload,
        ];
    }

    protected function config(string $key, $default = null)
    {
        $config = $this->gateway->config ?? [];

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }

        $credential = $this->gateway->credentials
            ->firstWhere('key_name', $key);

        return $credential?->key_value ?? $default;
    }
}
