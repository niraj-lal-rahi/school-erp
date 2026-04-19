<?php

namespace App\DataTransferObjects\SIS;

use Illuminate\Support\Str;

readonly class GuardianData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['uuid'] ??= (string) Str::uuid();
        $payload['full_name'] = trim(implode(' ', array_filter([
            $payload['first_name'] ?? null,
            $payload['middle_name'] ?? null,
            $payload['last_name'] ?? null,
        ])));

        return new self($payload);
    }
}
