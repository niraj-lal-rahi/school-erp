<?php

namespace App\Repositories\Eloquent\Reports;

use App\Models\Reports\ReportCache;
use App\Repositories\Contracts\Reports\ReportCacheRepositoryInterface;
use Illuminate\Support\Collection;

class ReportCacheRepository implements ReportCacheRepositoryInterface
{
    public function findValid(string $cacheKey): ?ReportCache
    {
        return ReportCache::query()
            ->where('cache_key', $cacheKey)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function store(array $attributes): ReportCache
    {
        ReportCache::query()->updateOrCreate(
            [
                'school_id' => $attributes['school_id'],
                'cache_key' => $attributes['cache_key'],
            ],
            [
                'data' => $attributes['data'],
                'expires_at' => $attributes['expires_at'],
                'created_at' => $attributes['created_at'] ?? now(),
            ],
        );

        return ReportCache::query()
            ->where('school_id', $attributes['school_id'])
            ->where('cache_key', $attributes['cache_key'])
            ->firstOrFail();
    }

    public function delete(ReportCache $reportCache): void
    {
        $reportCache->delete();
    }

    public function deleteByKey(string $cacheKey): void
    {
        ReportCache::query()
            ->where('cache_key', $cacheKey)
            ->delete();
    }

    public function purgeExpired(?string $before = null): int
    {
        $target = $before ?? now()->toDateTimeString();

        return ReportCache::query()
            ->where('expires_at', '<=', $target)
            ->delete();
    }

    public function list(array $filters = []): Collection
    {
        return ReportCache::query()
            ->when($filters['date_from'] ?? null, fn ($query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->orderByDesc('created_at')
            ->get();
    }
}
