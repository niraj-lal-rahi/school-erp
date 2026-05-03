<?php

namespace App\Http\Middleware;

use App\Services\Settings\LocalizationService;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantLocalization
{
    public function __construct(
        protected TenantContext $tenantContext,
        protected LocalizationService $localization,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = $this->tenantContext->id();

        if ($schoolId !== null) {
            $config = $this->localization->tenantConfig($schoolId);

            config([
                'app.timezone' => $config['timezone'],
                'app.locale' => $config['locale'],
            ]);

            date_default_timezone_set($config['timezone']);
            App::setLocale($config['locale']);
        }

        return $next($request);
    }
}
