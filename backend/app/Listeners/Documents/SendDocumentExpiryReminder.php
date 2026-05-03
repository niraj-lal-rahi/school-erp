<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentExpired;
use App\Services\Communication\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SendDocumentExpiryReminder implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(DocumentExpired $event): void
    {
        $recipient = $this->resolveRecipient($event->document);

        if ($recipient === null) {
            return;
        }

        $this->notifications->sendToRecipients(new Collection([$recipient]), [
            'school_id' => $event->document->school_id,
            'subject' => 'Document expired',
            'message' => sprintf('Document "%s" has expired.', $event->document->title),
            'channel' => 'in_app',
            'channels' => ['in_app'],
            'provider' => 'documents',
        ]);
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
