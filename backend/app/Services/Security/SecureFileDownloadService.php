<?php

namespace App\Services\Security;

use App\Models\Documents\DocumentFile;
use App\Models\Reports\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileDownloadService
{
    public function __construct(
        protected SensitiveActionAuditService $audit,
    ) {
    }

    public function temporaryDocumentUrl(DocumentFile $documentFile, int $minutes = 15): ?string
    {
        $disk = Storage::disk($documentFile->disk);

        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($documentFile->file_path, now()->addMinutes($minutes));
        }

        if (Route::has('documents.download')) {
            return URL::temporarySignedRoute('documents.download', now()->addMinutes($minutes), ['id' => $documentFile->document_id]);
        }

        return null;
    }

    public function downloadDocument(DocumentFile $documentFile, ?Request $request = null): StreamedResponse
    {
        $disk = $this->assertAllowedDisk($documentFile->disk);
        abort_unless(Storage::disk($disk)->exists($documentFile->file_path), 404, 'Requested document file was not found.');

        $this->audit->log('document.download.secure', [
            'document_file_id' => $documentFile->id,
            'document_id' => $documentFile->document_id,
            'disk' => $disk,
        ], $request);

        return Storage::disk($disk)->download(
            $documentFile->file_path,
            $this->safeFilename($documentFile->original_file_name)
        );
    }

    public function downloadReportExport(ReportExport $reportExport, ?Request $request = null, string $disk = 'local'): StreamedResponse
    {
        $disk = $this->assertAllowedDisk($disk);
        abort_unless(Storage::disk($disk)->exists($reportExport->file_path), 404, 'Requested report export was not found.');

        $this->audit->log('report.export.download', [
            'report_export_id' => $reportExport->id,
            'disk' => $disk,
        ], $request);

        return Storage::disk($disk)->download(
            $reportExport->file_path,
            $this->safeFilename($reportExport->file_name),
            array_filter([
                'Content-Type' => $reportExport->mime_type,
            ]),
        );
    }

    protected function assertAllowedDisk(string $disk): string
    {
        $allowed = ['local', 'private', 'public', 's3'];
        abort_unless(in_array($disk, $allowed, true), 403, 'File disk is not allowed for secure download.');

        return $disk;
    }

    protected function safeFilename(?string $filename): string
    {
        $filename = trim((string) $filename);

        if ($filename === '') {
            return 'download.bin';
        }

        return str_replace(["\r", "\n", '\\', '/'], '-', $filename);
    }
}
