<?php

namespace App\DataTransferObjects\HR;

readonly class StaffBankDetailData
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
