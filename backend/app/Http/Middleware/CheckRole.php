<?php

namespace App\Http\Middleware;

use App\Services\Rbac\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function __construct(
        protected AccessControlService $accessControl,
    ) {
    }

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Unauthenticated.');
        abort_if(! $this->accessControl->checkRole($user, $role), 403, 'Forbidden.');

        return $next($request);
    }
}
