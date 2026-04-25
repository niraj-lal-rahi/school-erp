<?php

namespace App\DataTransferObjects\Timetable;

readonly class TimetableSubstitutionData
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
