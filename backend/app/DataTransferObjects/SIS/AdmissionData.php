<?php

namespace App\DataTransferObjects\SIS;

use Illuminate\Support\Str;

readonly class AdmissionData
{
    public function __construct(
        public array $attributes,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $payload['uuid'] ??= (string) Str::uuid();
        $payload['application_status'] ??= 'draft';
        $payload['applied_on'] ??= now()->toDateString();
        $payload['status'] ??= $payload['application_status'] === 'converted' ? 'admitted' : 'applied';

        return new self($payload);
    }
}
