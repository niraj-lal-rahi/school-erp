<?php

namespace App\DataTransferObjects\SIS;

readonly class StudentNoteData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['visibility_type'] ??= 'internal';

        return new self($payload);
    }
}
