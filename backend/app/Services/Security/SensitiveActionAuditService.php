<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SensitiveActionAuditService
{
    public function log(string $action, array $context = [], ?Request $request = null, string $level = 'info'): void
    {
        $payload = array_filter([
            'action' => $action,
            'user_id' => $request?->user()?->id ?? Arr::get($context, 'user_id'),
            'school_id' => $request?->user()?->school_id ?? Arr::get($context, 'school_id'),
            'ip_address' => $request?->ip() ?? Arr::get($context, 'ip_address'),
            'user_agent' => $request?->userAgent() ?? Arr::get($context, 'user_agent'),
            'route' => $request?->path(),
            'method' => $request?->method(),
            'metadata' => $this->maskSensitive($context),
        ], fn ($value) => $value !== null);

        Log::log($level, '[security] '.$action, $payload);
    }

    protected function maskSensitive(array $context): array
    {
        $masked = [];

        foreach ($context as $key => $value) {
            $normalized = strtolower((string) $key);

            if (str_contains($normalized, 'password')
                || str_contains($normalized, 'secret')
                || str_contains($normalized, 'token')
                || str_contains($normalized, 'signature')
                || str_contains($normalized, 'authorization')
            ) {
                $masked[$key] = '***masked***';
                continue;
            }

            $masked[$key] = is_array($value) ? $this->maskSensitive($value) : $value;
        }

        return $masked;
    }
}
