<?php

namespace App\Contracts\Storage;

class FileScanResult
{
    public function __construct(
        public readonly bool $clean,
        public readonly ?string $message = null,
        public readonly array $metadata = [],
    ) {
    }

    public static function clean(array $metadata = []): self
    {
        return new self(true, null, $metadata);
    }

    public static function infected(string $message, array $metadata = []): self
    {
        return new self(false, $message, $metadata);
    }
}
