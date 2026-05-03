<?php

namespace App\Services\Documents;

use App\Events\Documents\DocumentRejected;
use App\Events\Documents\DocumentVerified;
use App\Models\Documents\Document;
use App\Models\Documents\DocumentVerification;
use App\Models\User;
use App\Repositories\Contracts\Documents\DocumentVerificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentVerificationService
{
    public function __construct(
        protected DocumentVerificationRepositoryInterface $verifications,
    ) {
    }

    public function pending(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->verifications->paginatePending($filters, $perPage);
    }

    public function history(Document $document): Collection
    {
        return $this->verifications->listByDocument($document->id);
    }

    public function submit(Document $document, ?string $remarks = null): DocumentVerification
    {
        return DB::transaction(function () use ($document, $remarks): DocumentVerification {
            return $this->verifications->create([
                'school_id' => $document->school_id,
                'document_id' => $document->id,
                'verified_by' => null,
                'status' => 'pending',
                'remarks' => $remarks,
                'verified_at' => null,
            ]);
        });
    }

    public function verify(Document $document, User $verifier, ?string $remarks = null): DocumentVerification
    {
        return DB::transaction(function () use ($document, $verifier, $remarks): DocumentVerification {
            $verification = $this->verifications->create([
                'school_id' => $document->school_id,
                'document_id' => $document->id,
                'verified_by' => $verifier->id,
                'status' => 'verified',
                'remarks' => $remarks,
                'verified_at' => now(),
            ]);

            $document->update(['verification_status' => 'verified']);
            DB::afterCommit(fn () => event(new DocumentVerified($document, $verification, $verifier, ['remarks' => $remarks])));

            return $verification;
        });
    }

    public function reject(Document $document, User $verifier, string $remarks): DocumentVerification
    {
        return DB::transaction(function () use ($document, $verifier, $remarks): DocumentVerification {
            $verification = $this->verifications->create([
                'school_id' => $document->school_id,
                'document_id' => $document->id,
                'verified_by' => $verifier->id,
                'status' => 'rejected',
                'remarks' => $remarks,
                'verified_at' => now(),
            ]);

            $document->update(['verification_status' => 'rejected']);
            DB::afterCommit(fn () => event(new DocumentRejected($document, $verification, $verifier, ['remarks' => $remarks])));

            return $verification;
        });
    }
}
