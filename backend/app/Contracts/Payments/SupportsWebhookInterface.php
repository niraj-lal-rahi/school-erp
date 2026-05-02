<?php

namespace App\Contracts\Payments;

interface SupportsWebhookInterface
{
    public function processWebhook(array $payload, ?string $signature = null): array;
}
