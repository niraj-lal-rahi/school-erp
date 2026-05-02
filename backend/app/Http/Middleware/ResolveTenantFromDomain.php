<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantFromDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $request->headers->has('X-Tenant-Code')
            && ! $request->headers->has('X-Tenant-Id')
            && ! $request->headers->has('X-Tenant-Domain')
            && $request->getHost()
        ) {
            $request->headers->set('X-Tenant-Domain', $request->getHost());
        }

        return $next($request);
    }
}
