<?php

namespace App\DataTransferObjects\Finance;

readonly class LedgerEntryData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }
}
