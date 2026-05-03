<?php

namespace App\Listeners\Documents;

use App\Events\Documents\DocumentDownloaded;
use App\Events\Documents\DocumentExpired;
use App\Events\Documents\DocumentRejected;
use App\Events\Documents\DocumentUploaded;
use App\Events\Documents\DocumentVerified;
use App\Services\Documents\DocumentAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogDocumentAction implements ShouldQueue
{
    public function __construct(
        protected DocumentAuditService $audits,
    ) {
    }

    public function handle(object $event): void
    {
        [$document, $action, $actor, $metadata, $ipAddress, $userAgent] = $this->resolvePayload($event);

        if (! $document || ! $action) {
            return;
        }

        $this->audits->log($document, $action, $actor, array_merge($metadata, [
            'event_logged' => true,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]));
    }

    protected function resolvePayload(object $event): array
    {
        return match (true) {
            $event instanceof DocumentUploaded => [$event->document, 'uploaded', $event->actor, array_merge($event->metadata, ['document_file_id' => $event->file->id, 'version_no' => $event->file->version_no]), $event->ipAddress, $event->userAgent],
            $event instanceof DocumentDownloaded => [$event->document, 'downloaded', $event->actor, $event->metadata, $event->ipAddress, $event->userAgent],
            $event instanceof DocumentVerified => [$event->document, 'verified', $event->actor, array_merge($event->metadata, ['verification_id' => $event->verification->id]), null, null],
            $event instanceof DocumentRejected => [$event->document, 'rejected', $event->actor, array_merge($event->metadata, ['verification_id' => $event->verification->id]), null, null],
            $event instanceof DocumentExpired => [$event->document, 'updated', null, array_merge($event->metadata, ['reason' => 'document_expired']), null, null],
            default => [null, null, null, [], null, null],
        };
    }
}
