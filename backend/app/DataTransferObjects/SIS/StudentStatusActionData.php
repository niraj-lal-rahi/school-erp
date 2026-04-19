<?php

namespace App\DataTransferObjects\SIS;

readonly class StudentStatusActionData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['effective_date'] ??= now()->toDateString();

        return new self($payload);
    }
}
