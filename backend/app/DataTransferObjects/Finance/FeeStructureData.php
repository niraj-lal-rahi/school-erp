<?php

namespace App\DataTransferObjects\Finance;

readonly class FeeStructureData
{
    public function __construct(
        public array $attributes,
        public array $items = [],
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            attributes: collect($payload)->except('items')->all(),
            items: $payload['items'] ?? [],
        );
    }
}
