<?php

namespace App\Services\Cache;

use App\Support\Cache\CacheKeyBuilder;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

class TenantCacheService
{
    public function __construct(
        protected CacheKeyBuilder $keys,
    ) {
    }

    public function remember(string $area, ?int $schoolId, array $segments, DateTimeInterface|DateInterval|int $ttl, callable $callback): mixed
    {
        return Cache::remember(
            $this->key($area, $schoolId, $segments, $this->namespaceVersion($area, $schoolId)),
            $ttl,
            $callback
        );
    }

    public function rememberForever(string $area, ?int $schoolId, array $segments, callable $callback): mixed
    {
        return Cache::rememberForever(
            $this->key($area, $schoolId, $segments, $this->namespaceVersion($area, $schoolId)),
            $callback
        );
    }

    public function forget(string $area, ?int $schoolId, array $segments = []): void
    {
        Cache::forget($this->key($area, $schoolId, $segments, $this->namespaceVersion($area, $schoolId)));
    }

    public function flushArea(string $area, ?int $schoolId): void
    {
        Cache::forever(
            $this->keys->versionKey($area, $schoolId),
            $this->namespaceVersion($area, $schoolId) + 1
        );
    }

    public function namespaceVersion(string $area, ?int $schoolId): int
    {
        return (int) Cache::rememberForever(
            $this->keys->versionKey($area, $schoolId),
            fn (): int => 1
        );
    }

    public function key(string $area, ?int $schoolId, array $segments = [], ?int $version = null): string
    {
        return $this->keys->build(
            $area,
            $schoolId,
            $segments,
            $version ?? $this->namespaceVersion($area, $schoolId)
        );
    }
}
