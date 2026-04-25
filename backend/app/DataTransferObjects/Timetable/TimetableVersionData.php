<?php

namespace App\DataTransferObjects\Timetable;

readonly class TimetableVersionData
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
