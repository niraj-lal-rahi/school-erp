<?php

namespace App\DataTransferObjects\Attendance;

readonly class AttendanceSummaryData
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
