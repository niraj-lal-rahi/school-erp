<?php

namespace App\Modules\SuperAdmin\Jobs;

use App\Modules\SuperAdmin\Services\TenantBackupService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class TenantBackupJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 1800;

    public function __construct(
        public int $backupLogId,
    ) {
        $this->onQueue(config('queue.routing.backups', QueueNames::DEFAULT));
    }

    public function backoff(): array
    {
        return JobRetryProfile::reports();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('tenant-backup:'.$this->backupLogId))
                ->releaseAfter(30)
                ->expireAfter(3600),
        ];
    }

    public function handle(TenantBackupService $backups): void
    {
        $backups->processBackupLog($this->backupLogId);
    }
}
