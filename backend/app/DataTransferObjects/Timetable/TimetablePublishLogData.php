<?php

namespace App\DataTransferObjects\Timetable;

readonly class TimetablePublishLogData
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
