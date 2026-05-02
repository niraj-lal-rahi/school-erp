<?php

namespace App\Services\Payments;

use App\Models\Payments\PaymentGateway;
use App\Repositories\Contracts\Payments\PaymentGatewayRepositoryInterface;
use App\Services\Payments\Gateways\ManualUpiPaymentGateway;
use App\Services\Payments\Gateways\OfflinePaymentGateway;
use App\Services\Payments\Gateways\RazorpayPaymentGateway;
use App\Services\Payments\Gateways\StripePaymentGateway;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PaymentGatewayService
{
    public function __construct(
        protected PaymentGatewayRepositoryInterface $gateways,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->gateways->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): PaymentGateway
    {
        return $this->gateways->findOrFail($id);
    }

    public function listForTenant(?int $schoolId): Collection
    {
        return $this->gateways->paginate(['school_id' => $schoolId], 100)->getCollection();
    }

    public function create(array $attributes): PaymentGateway
    {
        return DB::transaction(function () use ($attributes): PaymentGateway {
            $credentials = $attributes['credentials'] ?? [];
            unset($attributes['credentials']);

            $gateway = $this->gateways->create($attributes);

            foreach ($credentials as $credential) {
                $gateway->credentials()->updateOrCreate(
                    ['key_name' => $credential['key_name']],
                    [
                        'school_id' => $gateway->school_id,
                        'key_value' => $credential['key_value'] ?? null,
                        'is_encrypted' => (bool) ($credential['is_encrypted'] ?? true),
                    ]
                );
            }

            return $gateway->refresh()->load('credentials');
        });
    }

    public function update(PaymentGateway $gateway, array $attributes): PaymentGateway
    {
        return DB::transaction(function () use ($gateway, $attributes): PaymentGateway {
            $credentials = $attributes['credentials'] ?? null;
            unset($attributes['credentials']);

            $gateway = $this->gateways->update($gateway, $attributes);

            if (is_array($credentials)) {
                foreach ($credentials as $credential) {
                    $gateway->credentials()->updateOrCreate(
                        ['key_name' => $credential['key_name']],
                        [
                            'school_id' => $gateway->school_id,
                            'key_value' => $credential['key_value'] ?? null,
                            'is_encrypted' => (bool) ($credential['is_encrypted'] ?? true),
                        ]
                    );
                }
            }

            return $gateway->refresh()->load('credentials');
        });
    }

    public function delete(PaymentGateway $gateway): void
    {
        $this->gateways->delete($gateway);
    }

    public function listActiveForTenant(?int $schoolId): Collection
    {
        return $this->gateways->listActiveForTenant($schoolId);
    }

    public function getActiveGatewayForTenant(string $provider, ?int $schoolId = null): ?PaymentGateway
    {
        return $this->gateways->findActiveByProvider($provider, $schoolId);
    }

    public function resolveGatewayImplementation(PaymentGateway $gateway)
    {
        $implementation = match ($gateway->provider) {
            'razorpay' => app(RazorpayPaymentGateway::class),
            'stripe' => app(StripePaymentGateway::class),
            'upi_manual' => app(ManualUpiPaymentGateway::class),
            'offline' => app(OfflinePaymentGateway::class),
        };

        return $implementation->setGateway($gateway);
    }
}
