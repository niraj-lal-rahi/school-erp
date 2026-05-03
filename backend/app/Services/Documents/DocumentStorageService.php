<?php

namespace App\Services\Documents;

use App\Models\Documents\DocumentFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorageService
{
    public function uploadFile(UploadedFile $file, array $context, string $disk = 'local'): array
    {
        $extension = $file->getClientOriginalExtension();
        $generatedName = Str::uuid()->toString().($extension ? '.'.$extension : '');
        $path = $this->buildPrivatePath($context, $generatedName);

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
        $disk = Storage::disk($documentFile->disk);

        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($documentFile->file_path, now()->addMinutes($minutes));
        }

        if ($documentFile->disk === 'public') {
            return $disk->url($documentFile->file_path);
        }

        if (Route::has('documents.download')) {
            return URL::temporarySignedRoute('documents.download', now()->addMinutes($minutes), ['id' => $documentFile->document_id]);
        }

        return null;
    }

    public function downloadResponse(DocumentFile $documentFile): StreamedResponse
    {
        return Storage::disk($documentFile->disk)->download($documentFile->file_path, $documentFile->original_file_name);
    }

    public function delete(DocumentFile $documentFile): void
    {
        if (Storage::disk($documentFile->disk)->exists($documentFile->file_path)) {
            Storage::disk($documentFile->disk)->delete($documentFile->file_path);
        }
    }

    public function exists(DocumentFile $documentFile): bool
    {
        return Storage::disk($documentFile->disk)->exists($documentFile->file_path);
    }

    public function calculateChecksum(UploadedFile $file): string
    {
        return hash_file('sha256', $file->getRealPath());
    }

    protected function buildPrivatePath(array $context, string $generatedName): string
    {
        $schoolId = $context['school_id'] ?? 'shared';
        $ownerType = $context['owner_type'] ?? 'general';
        $ownerId = $context['owner_id'] ?? '0';
        $documentId = $context['document_id'] ?? 'new';

        return sprintf(
            'documents/%s/%s/%s/%s/%s',
            $schoolId,
            $ownerType,
            $ownerId,
            $documentId,
            $generatedName
        );
    }
}
