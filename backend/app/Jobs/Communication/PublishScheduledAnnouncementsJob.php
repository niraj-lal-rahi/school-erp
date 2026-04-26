<?php

namespace App\Jobs\Communication;

use App\Models\Communication\Announcement;
use App\Services\Communication\AnnouncementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishScheduledAnnouncementsJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $schoolId = null,
    ) {
    }

    public function handle(AnnouncementService $service): void
    {
        Announcement::query()
            ->when($this->schoolId, fn ($query) => $query->where('school_id', $this->schoolId))
            ->where('status', 'scheduled')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->get()
            ->each(function (Announcement $announcement) use ($service): void {
                $service->publish(
                    $announcement,
                    $announcement->published_by ?: $announcement->created_by,
                    ['publish_at' => $announcement->publish_at, 'expires_at' => $announcement->expires_at]
                );
            });
    }
}
