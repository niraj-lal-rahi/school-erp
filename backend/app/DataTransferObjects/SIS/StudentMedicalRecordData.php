<?php

namespace App\DataTransferObjects\SIS;

readonly class StudentMedicalRecordData
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
