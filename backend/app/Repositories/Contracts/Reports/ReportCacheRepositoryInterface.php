<?php

namespace App\Repositories\Contracts\Reports;

use App\Models\Reports\ReportCache;
use Illuminate\Support\Collection;

interface ReportCacheRepositoryInterface
{
    public function findValid(string $cacheKey): ?ReportCache;

    public function store(array $attributes): ReportCache;

    public function delete(ReportCache $reportCache): void;

    public function deleteByKey(string $cacheKey): void;

    public function purgeExpired(?string $before = null): int;

    public function list(array $filters = []): Collection;
}
