<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\Reports\ReportCacheRepositoryInterface;
use Illuminate\Support\Collection;

class ReportCacheService
{
    public function __construct(
        protected ReportCacheRepositoryInterface $cache,
    ) {
    }

    public function buildKey(string $namespace, array $payload = []): string
    {
        ksort($payload);

        return $namespace.':'.sha1(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function get(string $cacheKey): mixed
    {
        $entry = $this->cache->findValid($cacheKey);

        return $entry ? json_decode($entry->data, true) : null;
    }

    public function put(int $schoolId, string $cacheKey, mixed $data, int $ttlSeconds = 900): array
    {
        $entry = $this->cache->store([
            'school_id' => $schoolId,
            'cache_key' => $cacheKey,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'expires_at' => now()->addSeconds($ttlSeconds),
            'created_at' => now(),
        ]);

        return [
            'cache_key' => $entry->cache_key,
            'expires_at' => $entry->expires_at,
        ];
    }

    public function remember(int $schoolId, string $cacheKey, callable $callback, int $ttlSeconds = 900): mixed
    {
        $cached = $this->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $fresh = $callback();
        $this->put($schoolId, $cacheKey, $fresh, $ttlSeconds);

        return $fresh;
    }

    public function forget(string $cacheKey): void
    {
        $this->cache->deleteByKey($cacheKey);
    }

    public function purgeExpired(?string $before = null): int
    {
        return $this->cache->purgeExpired($before);
    }

    public function list(array $filters = []): Collection
    {
        return $this->cache->list($filters);
    }
}
