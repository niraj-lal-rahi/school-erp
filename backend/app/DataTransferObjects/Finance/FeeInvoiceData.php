<?php

namespace App\DataTransferObjects\Finance;

readonly class FeeInvoiceData
{
    public function __construct(
        public array $attributes,
        public array $installmentIds = [],
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            attributes: collect($payload)->except('installment_ids')->all(),
            installmentIds: $payload['installment_ids'] ?? [],
        );
    }
}
