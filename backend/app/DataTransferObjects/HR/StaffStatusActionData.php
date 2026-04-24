<?php

namespace App\DataTransferObjects\HR;

readonly class StaffStatusActionData
{
    public function __construct(
        public string $actionType,
        public string $newStatus,
        public ?string $reason = null,
        public ?string $effectiveDate = null,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            actionType: $payload['action_type'],
            newStatus: $payload['new_status'],
            reason: $payload['reason'] ?? null,
            effectiveDate: $payload['effective_date'] ?? null,
        );
    }
}
