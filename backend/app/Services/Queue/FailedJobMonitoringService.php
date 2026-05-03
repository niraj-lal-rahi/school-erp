<?php

namespace App\Services\Queue;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FailedJobMonitoringService
{
    public function summary(?int $hours = null): array
    {
        $hours ??= (int) config('queue.monitoring.failed_summary_window_hours', 24);
        $since = now()->subHours($hours);

        $rows = DB::table(config('queue.failed.table', 'failed_jobs'))
            ->selectRaw('queue, count(*) as failures, max(failed_at) as latest_failed_at')
            ->where('failed_at', '>=', $since)
            ->groupBy('queue')
            ->orderByDesc('failures')
            ->get();

        return [
            'window_hours' => $hours,
            'total_failures' => (int) $rows->sum('failures'),
            'queues' => $rows->map(fn ($row) => [
                'queue' => $row->queue,
                'failures' => (int) $row->failures,
                'latest_failed_at' => $row->latest_failed_at,
            ])->values()->all(),
        ];
    }

    public function recent(int $limit = 50): Collection
    {
        return DB::table(config('queue.failed.table', 'failed_jobs'))
            ->orderByDesc('failed_at')
            ->limit($limit)
            ->get();
    }
}
