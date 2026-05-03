<?php

namespace App\Services\Documents;

use App\Events\Documents\DocumentExpired;
use App\Models\Documents\Document;
use App\Repositories\Contracts\Documents\DocumentRepositoryInterface;
use App\Services\Communication\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentExpiryService
{
    public function __construct(
        protected DocumentRepositoryInterface $documents,
        protected NotificationService $notifications,
    ) {
    }

    public function expiringWithin(int $days = 30): Collection
    {
        return $this->documents->expiringBetween(now()->toDateString(), now()->addDays($days)->toDateString());
    }

    public function expired(): Collection
    {
        return $this->documents->expiredDocuments();
    }

    public function markExpired(): int
    {
        $count = 0;

        foreach ($this->expired() as $document) {
            if ($document->verification_status === 'expired') {
                continue;
            }

            DB::transaction(function () use ($document): void {
                $document->update(['verification_status' => 'expired']);
                DB::afterCommit(fn () => event(new DocumentExpired($document, ['expiry_date' => optional($document->expiry_date)->toDateString()])));
            });

            $count++;
        }

        return $count;
    }

    public function sendExpiryReminders(int $days = 30): int
    {
        $sent = 0;

        foreach ($this->expiringWithin($days) as $document) {
            $recipient = $this->buildRecipient($document);

            if ($recipient === null) {
                continue;
            }

            $this->notifications->sendToRecipients(collect([$recipient]), [
                'school_id' => $document->school_id,
                'channel' => 'in_app',
                'subject' => 'Document Expiry Reminder',
                'message' => sprintf('Document "%s" is expiring on %s.', $document->title, optional($document->expiry_date)->toDateString()),
            ]);

            $sent++;
        }

        return $sent;
    }

    protected function buildRecipient(Document $document): ?array
    {
        return match ($document->owner_type) {
            'user' => ['recipient_type' => 'user', 'recipient_id' => $document->owner_id, 'recipient' => $document->ownerUser],
            'student' => ['recipient_type' => 'student', 'recipient_id' => $document->owner_id, 'recipient' => $document->ownerStudent],
            'guardian' => ['recipient_type' => 'guardian', 'recipient_id' => $document->owner_id, 'recipient' => $document->ownerGuardian],
            'staff' => ['recipient_type' => 'staff', 'recipient_id' => $document->owner_id, 'recipient' => $document->ownerStaff],
            default => null,
        };
    }
}
