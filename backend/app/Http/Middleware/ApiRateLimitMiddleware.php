<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    public function __construct(
        protected RateLimiter $limiter,
    ) {
    }

    public function handle(Request $request, Closure $next, string $profile = 'api'): Response
    {
        [$maxAttempts, $decaySeconds] = $this->resolveProfile($profile);
        $key = $this->resolveKey($request, $profile);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return new JsonResponse([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->limiter->availableIn($key),
            ], 429);
        }

        $this->limiter->hit($key, $decaySeconds);
        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, $maxAttempts - $this->limiter->attempts($key)));

        return $response;
    }

    protected function resolveProfile(string $profile): array
    {
        return match ($profile) {
            'login' => [5, 60],
            'webhook' => [60, 60],
            'downloads' => [60, 60],
            default => [120, 60],
        };
    }

    protected function resolveKey(Request $request, string $profile): string
    {
        $tenant = $request->user()?->school_id
            ?? $request->header('X-Tenant-Id')
            ?? $request->header('X-Tenant-Code')
            ?? 'public';

        $principal = match ($profile) {
            'login' => strtolower((string) $request->input('email', $request->ip())),
            'webhook' => strtolower((string) $request->path()),
            default => (string) ($request->user()?->id ?? $request->ip()),
        };

        return sprintf('rate-limit:%s:%s:%s', $profile, $tenant, sha1($principal.'|'.$request->ip()));
    }
}
