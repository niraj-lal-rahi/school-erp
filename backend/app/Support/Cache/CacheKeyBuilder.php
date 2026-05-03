<?php

namespace App\Support\Cache;

class CacheKeyBuilder
{
    public function build(string $area, ?int $schoolId = null, array $segments = [], ?int $version = null): string
    {
        $parts = [
            'school-erp',
            'cache',
            $schoolId === null ? 'global' : 'tenant',
            (string) ($schoolId ?? 'global'),
            $this->normalize($area),
        ];

        if ($version !== null) {
            $parts[] = 'v'.$version;
        }

        foreach ($segments as $key => $segment) {
            if ($segment === null || $segment === '') {
                continue;
            }

            if (! is_int($key)) {
                $parts[] = $this->normalize($key);
            }

            $parts[] = $this->normalize($segment);
        }

        return implode(':', $parts);
    }

    public function versionKey(string $area, ?int $schoolId = null): string
    {
        return implode(':', [
            'school-erp',
            'cache-meta',
            $schoolId === null ? 'global' : 'tenant',
            (string) ($schoolId ?? 'global'),
            $this->normalize($area),
            'version',
        ]);
    }

    protected function normalize(mixed $segment): string
    {
        if (is_bool($segment)) {
            return $segment ? 'true' : 'false';
        }

        if (is_array($segment)) {
            $segment = json_encode($segment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $value = strtolower(trim((string) $segment));
        $value = preg_replace('/[^a-z0-9_.-]+/i', '-', $value) ?: 'segment';

        return trim($value, '-');
    }
}
