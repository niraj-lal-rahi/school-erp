<?php

namespace App\DataTransferObjects\SIS;

use Illuminate\Http\UploadedFile;

readonly class StudentDocumentData
{
    public function __construct(
        public string $documentType,
        public string $title,
        public UploadedFile $file,
        public array $metadata = [],
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            documentType: $payload['document_type'],
            title: $payload['title'],
            file: $payload['file'],
            metadata: [
                ...($payload['metadata'] ?? []),
                'issued_by' => $payload['issued_by'] ?? null,
                'issued_date' => $payload['issued_date'] ?? null,
                'expiry_date' => $payload['expiry_date'] ?? null,
                'verification_status' => $payload['verification_status'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ],
        );
    }
}
