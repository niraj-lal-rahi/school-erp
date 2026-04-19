<?php

namespace App\Http\Middleware;

use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Support\Multitenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        protected SchoolRepositoryInterface $schools,
        protected TenantContext $tenantContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $identifier = $request->header('X-Tenant-Code')
            ?: $request->header('X-Tenant-Id')
            ?: $request->header('X-Tenant-Domain')
            ?: $request->user()?->school?->code;

        abort_if(! $identifier, 400, 'Tenant identifier is required.');

        $school = $this->schools->findByIdentifier($identifier);
        abort_if(! $school, 404, 'Tenant not found.');

        if ($request->user() && $request->user()->school_id !== $school->id) {
            abort(403, 'Authenticated user does not belong to this tenant.');
        }

        $this->tenantContext->set($school);

        return $next($request);
    }
}
