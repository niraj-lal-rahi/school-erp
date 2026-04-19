<?php

namespace App\DataTransferObjects\Auth;

readonly class LoginData
{
    public function __construct(
        public string $tenantCode,
        public string $email,
        public string $password,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            tenantCode: $payload['tenant_code'],
            email: $payload['email'],
            password: $payload['password'],
        );
    }
}
