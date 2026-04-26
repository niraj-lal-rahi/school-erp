<?php

namespace App\Events\Communication;

use App\Models\Communication\Announcement;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublished
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public array $channels = ['in_app'],
    ) {
    }
}
