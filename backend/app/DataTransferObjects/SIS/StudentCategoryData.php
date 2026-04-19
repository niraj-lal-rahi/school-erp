<?php

namespace App\DataTransferObjects\SIS;

use Illuminate\Support\Str;

readonly class StudentCategoryData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self([
            ...$payload,
            'uuid' => $payload['uuid'] ?? (string) Str::uuid(),
        ]);
    }
}
