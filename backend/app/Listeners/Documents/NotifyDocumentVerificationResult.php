<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentRejected;
use App\Events\Documents\DocumentVerified;
use App\Services\Communication\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class NotifyDocumentVerificationResult implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(object $event): void
    {
        [$document, $subject, $message] = $this->payload($event);

        if (! $document || ! $message) {
            return;
        }

        $recipient = $this->resolveRecipient($document);

        if ($recipient === null) {
            return;
        }

        $this->notifications->sendToRecipients(new Collection([$recipient]), [
            'school_id' => $document->school_id,
            'subject' => $subject,
            'message' => $message,
            'channel' => 'in_app',
            'channels' => ['in_app'],
            'provider' => 'documents',
        ]);
    }

    protected function payload(object $event): array
    {
        return match (true) {
            $event instanceof DocumentVerified => [
                $event->document,
                'Document verified',
                sprintf('Document "%s" has been verified.', $event->document->title),
            ],
            $event instanceof DocumentRejected => [
                $event->document,
                'Document rejected',
                sprintf('Document "%s" has been rejected.', $event->document->title),
            ],
            default => [null, null, null],
        };
    }

    protected function resolveRecipient($document): ?array
    {
        return match ($document->owner_type) {
            'user' => $document->ownerUser ? ['recipient_type' => \App\Models\User::class, 'recipient_id' => $document->ownerUser->id, 'recipient' => $document->ownerUser] : null,
            'student' => $document->ownerStudent ? ['recipient_type' => 'student', 'recipient_id' => $document->ownerStudent->id, 'recipient' => $document->ownerStudent] : null,
            'guardian' => $document->ownerGuardian ? ['recipient_type' => 'guardian', 'recipient_id' => $document->ownerGuardian->id, 'recipient' => $document->ownerGuardian] : null,
            'staff' => $document->ownerStaff ? ['recipient_type' => 'staff', 'recipient_id' => $document->ownerStaff->id, 'recipient' => $document->ownerStaff] : null,
            default => null,
        };
    }
}
