<?php

namespace App\Listeners\Communication;

use App\Events\Communication\AnnouncementPublished;
use App\Services\Communication\NotificationService;

class QueueAnnouncementNotifications
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(AnnouncementPublished $event): void
    {
        $announcement = $event->announcement->loadMissing('recipients');

        $recipients = $announcement->recipients->map(fn ($recipient) => [
            'recipient_type' => $recipient->recipient_type,
            'recipient_id' => (int) $recipient->recipient_id,
            'recipient' => $recipient->resolveRecipient(),
        ]);

        $this->notifications->sendToRecipients($recipients, [
            'school_id' => $announcement->school_id,
            'subject' => $announcement->title,
            'message' => $announcement->content,
            'channels' => $event->channels,
        ]);
    }
}
