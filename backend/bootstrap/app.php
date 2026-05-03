<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.domain' => \App\Http\Middleware\ResolveTenantFromDomain::class,
            'tenant.resolve' => \App\Http\Middleware\ResolveTenant::class,
            'tenant.audit' => \App\Http\Middleware\TenantIsolationAuditMiddleware::class,
            'tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'tenant.feature' => \App\Http\Middleware\CheckTenantFeature::class,
            'tenant.limit' => \App\Http\Middleware\CheckTenantUsageLimit::class,
            'tenant.localization' => \App\Http\Middleware\ApplyTenantLocalization::class,
            'feature' => \App\Http\Middleware\CheckFeatureFlag::class,
            'api.rate' => \App\Http\Middleware\ApiRateLimitMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
