<?php

namespace App\Services\Documents;

use App\Jobs\Documents\ProcessDocumentBulkUploadJob;
use App\Models\Documents\DocumentBulkUpload;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentBulkUploadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DocumentBulkUploadService
{
    public function __construct(
        protected DocumentBulkUploadRepositoryInterface $bulkUploads,
        protected DocumentStorageService $storage,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->bulkUploads->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): DocumentBulkUpload
    {
        return $this->bulkUploads->findOrFail($id);
    }

    public function createRequest(array $attributes, UploadedFile $file, ?User $actor = null): DocumentBulkUpload
    {
        return DB::transaction(function () use ($attributes, $file, $actor): DocumentBulkUpload {
            $stored = $this->storage->uploadFile($file, [
                'school_id' => $attributes['school_id'],
                'owner_type' => 'general',
                'owner_id' => $actor?->id ?? 0,
                'document_id' => 'bulk',
            ], $attributes['disk'] ?? 'local');

            return $this->bulkUploads->create([
                'school_id' => $attributes['school_id'],
                'upload_type' => $attributes['upload_type'],
                'file_path' => $stored['file_path'],
                'status' => 'pending',
                'total_files' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'error_log' => null,
                'uploaded_by' => $actor?->id,
            ]);
        });
    }

    public function queueUpload(array $attributes, UploadedFile $file, ?User $actor = null): DocumentBulkUpload
    {
        return DB::transaction(function () use ($attributes, $file, $actor): DocumentBulkUpload {
            $bulkUpload = $this->createRequest($attributes, $file, $actor);

            DB::afterCommit(fn () => dispatch(new ProcessDocumentBulkUploadJob($bulkUpload->id)));

            return $bulkUpload;
        });
    }

    public function validateMapping(array $mapping = []): bool
    {
        return is_array($mapping);
    }

    public function markProcessing(DocumentBulkUpload $bulkUpload): DocumentBulkUpload
    {
        return DB::transaction(fn (): DocumentBulkUpload => $this->bulkUploads->update($bulkUpload, ['status' => 'processing']));
    }

    public function markCompleted(DocumentBulkUpload $bulkUpload, array $stats): DocumentBulkUpload
    {
        return DB::transaction(fn (): DocumentBulkUpload => $this->bulkUploads->update($bulkUpload, [
            'status' => 'completed',
            'total_files' => (int) ($stats['total_files'] ?? 0),
            'success_count' => (int) ($stats['success_count'] ?? 0),
            'failed_count' => (int) ($stats['failed_count'] ?? 0),
            'error_log' => $stats['error_log'] ?? null,
        ]));
    }

    public function markFailed(DocumentBulkUpload $bulkUpload, string $errorLog): DocumentBulkUpload
    {
        return DB::transaction(fn (): DocumentBulkUpload => $this->bulkUploads->update($bulkUpload, [
            'status' => 'failed',
            'error_log' => $errorLog,
        ]));
    }
}
