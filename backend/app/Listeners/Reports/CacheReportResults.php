<?php

namespace App\Listeners\Reports;

use App\Events\Reports\ReportRunCompleted;
use App\Services\Reports\ReportCacheService;

class CacheReportResults
{
    public function __construct(
        protected ReportCacheService $cache,
    ) {
    }

    public function handle(ReportRunCompleted $event): void
    {
        $cacheKey = $event->context['completed_cache_key']
            ?? $this->cache->buildKey('reports.completed_run.'.$event->reportRun->id, [
                'report_definition_id' => $event->reportRun->report_definition_id,
                'run_type' => $event->reportRun->run_type,
            ]);

        $this->cache->put($event->reportRun->school_id, $cacheKey, [
            'report_run_id' => $event->reportRun->id,
            'report_definition_id' => $event->reportRun->report_definition_id,
            'file_path' => $event->reportRun->file_path,
            'file_type' => $event->reportRun->file_type,
            'data' => $event->data,
        ], 3600);
    }
}
