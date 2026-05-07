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
            'legacy.tenant.resolve' => \App\Http\Middleware\ResolveTenant::class,
            'tenant.audit' => \App\Http\Middleware\TenantIsolationAuditMiddleware::class,
            'tenant.active' => \App\Modules\Tenant\Middleware\EnsureTenantActive::class,
            'legacy.tenant.active' => \App\Http\Middleware\EnsureTenantActive::class,
            'resolve.tenant' => \App\Modules\Tenant\Middleware\ResolvePlatformTenant::class,
            'platform.tenant.resolve' => \App\Modules\Tenant\Middleware\ResolvePlatformTenant::class,
            'switch.tenant.database' => \App\Modules\Tenant\Middleware\SwitchTenantDatabase::class,
            'tenant.platform.active' => \App\Modules\Tenant\Middleware\EnsureTenantActive::class,
            'ensure.platform.admin' => \App\Modules\SuperAdmin\Middleware\EnsurePlatformAdmin::class,
            'tenant.feature' => \App\Modules\Tenant\Middleware\CheckTenantFeature::class,
            'emergency.access' => \App\Modules\Tenant\Middleware\EmergencyAccessMiddleware::class,
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
