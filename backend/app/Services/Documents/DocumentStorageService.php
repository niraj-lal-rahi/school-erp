<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentFile;
use App\Services\Security\FileSecurityService;
use App\Services\Security\SecureFileDownloadService;
use App\Services\Storage\StoragePathBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorageService
{
    public function __construct(
        protected SecureFileDownloadService $downloads,
        protected FileSecurityService $security,
        protected StoragePathBuilder $paths,
    ) {
    }

    public function uploadFile(UploadedFile $file, array $context, string $disk = 'local'): array
    {
        $disk = $this->security->resolveDisk($disk);
        $this->security->assertUploadIsSafe($file, $context);

        $extension = $file->getClientOriginalExtension();
        $generatedName = Str::uuid()->toString().($extension ? '.'.$extension : '');
        $path = $this->paths->documentPath($context, $generatedName);

        Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));

        return [
            'file_name' => $generatedName,
            'original_file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'checksum' => $this->calculateChecksum($file),
        ];
    }

    public function temporaryDownloadUrl(DocumentFile $documentFile, int $minutes = 15): ?string
    {
        return $this->security->temporaryDownloadUrl($documentFile, $minutes);
    }

    public function downloadResponse(DocumentFile $documentFile, ?Request $request = null): StreamedResponse
    {
        return $this->security->download($documentFile, $request);
    }

    public function delete(DocumentFile $documentFile): void
    {
        $this->security->deletePhysicalFile($documentFile);
    }

    public function exists(DocumentFile $documentFile): bool
    {
        return Storage::disk($documentFile->disk)->exists($documentFile->file_path);
    }

    public function calculateChecksum(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

}
