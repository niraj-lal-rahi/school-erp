<?php

namespace App\Services\Documents;

use App\Events\Documents\DocumentDownloaded;
use App\Events\Documents\DocumentUploaded;
use App\Models\Documents\Document;
use App\Models\Documents\DocumentFile;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentFileRepositoryInterface;
use App\Repositories\Contracts\Documents\DocumentRepositoryInterface;
use App\Repositories\Contracts\Documents\DocumentTagRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentService
{
    public function __construct(
        protected DocumentRepositoryInterface $documents,
        protected DocumentFileRepositoryInterface $files,
        protected DocumentVersionService $versions,
        protected DocumentPermissionService $permissions,
        protected DocumentVerificationService $verificationService,
        protected DocumentAuditService $audits,
        protected DocumentTagRepositoryInterface $tags,
        protected DocumentStorageService $storage,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->documents->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Document
    {
        return $this->documents->findOrFail($id);
    }

    public function createWithUpload(array $attributes, UploadedFile $file, ?User $actor = null, ?Request $request = null): Document
    {
        return DB::transaction(function () use ($attributes, $file, $actor, $request): Document {
            $document = $this->documents->create([
                'school_id' => $attributes['school_id'],
                'category_id' => $attributes['category_id'] ?? null,
                'folder_id' => $attributes['folder_id'] ?? null,
                'owner_type' => $attributes['owner_type'],
                'owner_id' => $attributes['owner_id'] ?? null,
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'document_no' => $attributes['document_no'] ?? null,
                'issue_date' => $attributes['issue_date'] ?? null,
                'expiry_date' => $attributes['expiry_date'] ?? null,
                'verification_status' => $attributes['verification_status'] ?? 'pending',
                'status' => $attributes['status'] ?? 'active',
                'created_by' => $actor?->id,
            ]);

            $documentFile = $this->versions->uploadNewVersion($document, $file, ['disk' => $attributes['disk'] ?? 'local'], $actor);

            if (! empty($attributes['tags'])) {
                $this->tags->syncDocumentTags($document->id, (int) $document->school_id, $attributes['tags']);
            }

            if (! empty($attributes['permissions'])) {
                $this->permissions->replace($document, $attributes['permissions'], $actor);
            }

            if ($document->category?->requires_verification ?? false) {
                $this->verificationService->submit($document, 'Verification requested on upload.');
            }

            DB::afterCommit(fn () => event(DocumentUploaded::fromRequest($document, $documentFile, $actor, ['document_no' => $document->document_no], $request)));

            return $this->documents->findOrFail($document->id);
        });
    }

    public function update(Document $document, array $attributes, ?User $actor = null, ?Request $request = null): Document
    {
        return DB::transaction(function () use ($document, $attributes, $actor, $request): Document {
            $document = $this->documents->update($document, $attributes);

            if (array_key_exists('tags', $attributes)) {
                $this->tags->syncDocumentTags($document->id, (int) $document->school_id, $attributes['tags'] ?? []);
            }

            $this->audits->log($document, 'updated', $actor, ['fields' => array_keys($attributes)], $request);

            return $document;
        });
    }

    public function uploadVersion(Document $document, UploadedFile $file, array $attributes = [], ?User $actor = null): DocumentFile
    {
        $documentFile = $this->versions->uploadNewVersion($document, $file, $attributes, $actor);

        DB::afterCommit(fn () => event(new DocumentUploaded($document, $documentFile, $actor, ['version_upload' => true])));

        return $documentFile;
    }

    public function versionHistory(Document $document): Collection
    {
        return $this->versions->history($document);
    }

    public function currentFile(Document $document): ?DocumentFile
    {
        return $this->files->currentForDocument($document->id);
    }

    public function download(Document $document, ?User $actor = null, ?Request $request = null): StreamedResponse
    {
        $file = $this->currentFile($document);

        abort_if($file === null, 404, 'No current document file found.');

        $this->recordDownload($document, $actor, $request, ['document_file_id' => $file->id, 'version_no' => $file->version_no]);

        return $this->storage->downloadResponse($file, $request);
    }

    public function archive(Document $document, ?User $actor = null, ?Request $request = null): Document
    {
        return DB::transaction(function () use ($document, $actor, $request): Document {
            $document = $this->documents->update($document, ['status' => 'archived']);
            $this->audits->log($document, 'updated', $actor, ['status' => 'archived'], $request);

            return $document;
        });
    }

    public function delete(Document $document, ?User $actor = null, ?Request $request = null): void
    {
        DB::transaction(function () use ($document, $actor, $request): void {
            $this->documents->update($document, ['status' => 'deleted']);
            $this->documents->delete($document);
            $this->audits->log($document, 'deleted', $actor, ['status' => 'deleted'], $request);
        });
    }

    public function restore(Document $document, ?User $actor = null, ?Request $request = null): Document
    {
        return DB::transaction(function () use ($document, $actor, $request): Document {
            $document->restore();
            $document = $this->documents->update($document, ['status' => 'active']);
            $this->audits->log($document, 'restored', $actor, ['status' => 'active'], $request);

            return $document;
        });
    }

    public function auditLogs(Document $document): Collection
    {
        return $this->audits->listByDocument($document->id);
    }

    public function recordView(Document $document, ?User $actor = null, ?Request $request = null): void
    {
        $this->audits->log($document, 'viewed', $actor, [], $request);
    }

    public function recordDownload(Document $document, ?User $actor = null, ?Request $request = null, array $metadata = []): void
    {
        DB::afterCommit(fn () => event(DocumentDownloaded::fromRequest($document, $actor, $metadata, $request)));
    }

    public function verificationStatusSummary(?int $schoolId = null): array
    {
        $query = Document::withoutGlobalScopes()
            ->when($schoolId !== null, fn (Builder $builder) => $builder->where('school_id', $schoolId));

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('verification_status', 'pending')->count(),
            'verified' => (clone $query)->where('verification_status', 'verified')->count(),
            'rejected' => (clone $query)->where('verification_status', 'rejected')->count(),
            'expired' => (clone $query)->where('verification_status', 'expired')->count(),
        ];
    }
}
