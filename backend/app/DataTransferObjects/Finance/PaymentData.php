<?php

namespace App\DataTransferObjects\Finance;

readonly class PaymentData
{
    public function __construct(
        public array $attributes,
        public array $allocations = [],
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            attributes: collect($payload)->except('allocations')->all(),
            allocations: $payload['allocations'] ?? [],
        );
    }
}
