<?php

namespace App\Modules\SuperAdmin\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SystemErrorLogService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recent(int $limit = 50): Collection
    {
        $logDirectory = storage_path('logs');

        if (! File::isDirectory($logDirectory)) {
            return collect();
        }

        $files = collect(File::files($logDirectory))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values();

        $entries = collect();

        foreach ($files as $file) {
            $lines = preg_split('/\r\n|\r|\n/', File::get($file->getPathname())) ?: [];

            foreach (array_reverse($lines) as $index => $line) {
                if (! $this->isErrorLine($line)) {
                    continue;
                }

                $entries->push([
                    'file' => $file->getFilename(),
                    'line_number' => count($lines) - $index,
                    'level' => $this->extractLevel($line),
                    'message' => trim($line),
                    'logged_at' => $this->extractTimestamp($line),
                ]);

                if ($entries->count() >= $limit) {
                    return $entries->take($limit)->values();
                }
            }
        }

        return $entries->take($limit)->values();
    }

    protected function isErrorLine(string $line): bool
    {
        $normalized = strtolower($line);

        return str_contains($normalized, '.error:')
            || str_contains($normalized, ' production.error: ')
            || str_contains($normalized, ' local.error: ')
            || str_contains($normalized, ' critical: ')
            || str_contains($normalized, ' emergency: ');
    }

    protected function extractLevel(string $line): string
    {
        $normalized = strtolower($line);

        return match (true) {
            str_contains($normalized, 'critical') => 'critical',
            str_contains($normalized, 'emergency') => 'emergency',
            default => 'error',
        };
    }

    protected function extractTimestamp(string $line): ?string
    {
        if (preg_match('/\[(.*?)\]/', $line, $matches) !== 1) {
            return null;
        }

        return $matches[1] ?? null;
    }
}
