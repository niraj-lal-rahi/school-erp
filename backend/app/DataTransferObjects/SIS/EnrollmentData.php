<?php

namespace App\DataTransferObjects\SIS;

readonly class EnrollmentData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['status'] ??= 'enrolled';
        $payload['is_current'] ??= true;
        $payload['enrollment_date'] ??= $payload['joined_on'] ?? now()->toDateString();

        return new self($payload);
    }
}
