<?php

namespace App\Http\Middleware;

use App\Services\Rbac\AccessControlService;
use App\Services\Security\SensitiveActionAuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(
        protected AccessControlService $accessControl,
        protected SensitiveActionAuditService $audit,
    ) {
    }

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Unauthenticated.');

        if (! $this->accessControl->checkPermission($user, $permission)) {
            $this->audit->log('permission.denied', [
                'permission' => $permission,
                'user_id' => $user->id,
                'school_id' => $user->school_id,
            ], $request, 'warning');

            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
