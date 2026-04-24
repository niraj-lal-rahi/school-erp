<?php

namespace App\DataTransferObjects\HR;

readonly class SalaryStructureData
{
    public function __construct(
        public array $attributes,
        public array $items = [],
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $items = $payload['items'] ?? [];
        unset($payload['items']);

        return new self($payload, $items);
    }
}
