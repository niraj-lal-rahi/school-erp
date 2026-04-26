<?php

namespace App\Services\Communication;

use App\Events\Communication\AnnouncementPublished;
use App\Models\Communication\Announcement;
use App\Models\Communication\AnnouncementRecipient;
use App\Models\Communication\MessageAttachment;
use App\Repositories\Contracts\Communication\AnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnnouncementService
{
    public function __construct(
        protected AnnouncementRepositoryInterface $announcements,
        protected RecipientResolverService $recipientResolver,
        protected NotificationService $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->announcements->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): Announcement
    {
        return $this->announcements->findOrFail($id);
    }

    public function create(array $attributes): Announcement
    {
        return DB::transaction(function () use ($attributes): Announcement {
            $announcement = $this->announcements->create($this->baseAnnouncementPayload($attributes));

            $this->syncRecipients($announcement, $attributes);
            $this->syncAttachments($announcement, $attributes['attachments_meta'] ?? [], $attributes['uploaded_by'] ?? null);

            return $this->announcements->findOrFail($announcement->id);
        });
    }

    public function update(Announcement $announcement, array $attributes): Announcement
    {
        return DB::transaction(function () use ($announcement, $attributes): Announcement {
            $announcement = $this->announcements->update($announcement, $this->baseAnnouncementPayload($attributes));

            if (array_key_exists('recipients', $attributes) || array_key_exists('audience_type', $attributes)) {
                $this->syncRecipients($announcement, array_merge($announcement->toArray(), $attributes));
            }

            if (array_key_exists('attachments_meta', $attributes)) {
                $this->syncAttachments($announcement, $attributes['attachments_meta'] ?? [], $attributes['uploaded_by'] ?? null);
            }

            return $this->announcements->findOrFail($announcement->id);
        });
    }

    public function delete(Announcement $announcement): void
    {
        DB::transaction(function () use ($announcement): void {
            $this->announcements->delete($announcement);
        });
    }

    public function schedule(Announcement $announcement, array $attributes = []): Announcement
    {
        return DB::transaction(fn (): Announcement => $this->announcements->update($announcement, [
            'status' => 'scheduled',
            'publish_at' => $attributes['publish_at'] ?? $announcement->publish_at,
            'expires_at' => $attributes['expires_at'] ?? $announcement->expires_at,
            'priority' => $attributes['priority'] ?? $announcement->priority,
        ]));
    }

    public function publish(Announcement $announcement, ?int $publishedBy, array $attributes = []): Announcement
    {
        return DB::transaction(function () use ($announcement, $publishedBy, $attributes): Announcement {
            $announcement = $this->announcements->update($announcement, [
                'status' => 'published',
                'published_by' => $publishedBy,
                'published_at' => now(),
                'publish_at' => $attributes['publish_at'] ?? $announcement->publish_at ?? now(),
                'expires_at' => $attributes['expires_at'] ?? $announcement->expires_at,
                'priority' => $attributes['priority'] ?? $announcement->priority,
            ]);

            $channels = $attributes['channels'] ?? ['in_app'];

            event(new AnnouncementPublished($announcement->refresh(), $channels));

            return $this->announcements->findOrFail($announcement->id);
        });
    }

    public function cancel(Announcement $announcement): Announcement
    {
        return DB::transaction(fn (): Announcement => $this->announcements->update($announcement, [
            'status' => 'cancelled',
        ]));
    }

    public function recipientsForAnnouncement(Announcement $announcement): Collection
    {
        $announcement->loadMissing('recipients');

        return $announcement->recipients->map(function (AnnouncementRecipient $recipient): array {
            return [
                'recipient_type' => $recipient->recipient_type,
                'recipient_id' => (int) $recipient->recipient_id,
                'recipient' => $recipient->resolveRecipient(),
            ];
        });
    }

    protected function syncRecipients(Announcement $announcement, array $attributes): void
    {
        $recipients = $this->recipientResolver->resolve($attributes['audience_type'], [
            'academic_year_id' => $attributes['academic_year_id'] ?? null,
            'class_id' => $attributes['class_id'] ?? null,
            'section_id' => $attributes['section_id'] ?? null,
            'recipients' => $attributes['recipients'] ?? [],
        ]);

        $announcement->recipients()->delete();

        foreach ($recipients as $recipient) {
            $announcement->recipients()->create([
                'school_id' => $announcement->school_id,
                'recipient_type' => $recipient['recipient_type'],
                'recipient_id' => $recipient['recipient_id'],
            ]);
        }
    }

    protected function syncAttachments(Announcement $announcement, array $attachments, ?int $uploadedBy = null): void
    {
        if ($attachments === []) {
            return;
        }

        foreach ($attachments as $attachment) {
            MessageAttachment::query()->create([
                'school_id' => $announcement->school_id,
                'announcement_id' => $announcement->id,
                'file_name' => $attachment['file_name'],
                'file_path' => $attachment['file_path'],
                'mime_type' => $attachment['mime_type'] ?? null,
                'file_size' => $attachment['file_size'] ?? null,
                'uploaded_by' => $uploadedBy,
            ]);
        }
    }

    protected function baseAnnouncementPayload(array $attributes): array
    {
        return collect($attributes)->except([
            'recipients',
            'channels',
            'attachments',
            'attachments_meta',
            'uploaded_by',
        ])->all();
    }
}
