<?php

namespace App\DataTransferObjects\HR;

readonly class StaffData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['full_name'] = trim(implode(' ', array_filter([
            $payload['first_name'] ?? null,
            $payload['middle_name'] ?? null,
            $payload['last_name'] ?? null,
        ])));

        return new self($payload);
    }
}
