<?php

namespace App\Services\Security;

use App\Contracts\Storage\FileScanInterface;
use App\Models\Documents\DocumentFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileSecurityService
{
    public function __construct(
        protected FileScanInterface $scanner,
        protected SecureFileDownloadService $downloads,
    ) {
    }

    public function resolveDisk(?string $requestedDisk = null): string
    {
        $disk = $requestedDisk ?: 'private';

        if (! in_array($disk, ['private', 'local', 'public', 's3'], true)) {
            throw ValidationException::withMessages([
                'disk' => ['The selected storage disk is not supported.'],
            ]);
        }

        return $disk;
    }

    public function assertUploadIsSafe(UploadedFile $file, array $context = []): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['The uploaded file is not valid.'],
            ]);
        }

        $result = $this->scanner->scan($file, $context);

        if (! $result->clean) {
            throw ValidationException::withMessages([
                'file' => [$result->message ?: 'The uploaded file failed the security scan.'],
            ]);
        }
    }

    public function temporaryDownloadUrl(DocumentFile $documentFile, int $minutes = 15): ?string
    {
        return $this->downloads->temporaryDocumentUrl($documentFile, $minutes);
    }

    public function download(DocumentFile $documentFile, ?Request $request = null): StreamedResponse
    {
        return $this->downloads->downloadDocument($documentFile, $request);
    }

    public function deletePhysicalFile(DocumentFile $documentFile): void
    {
        if (Storage::disk($documentFile->disk)->exists($documentFile->file_path)) {
            Storage::disk($documentFile->disk)->delete($documentFile->file_path);
        }
    }
}
