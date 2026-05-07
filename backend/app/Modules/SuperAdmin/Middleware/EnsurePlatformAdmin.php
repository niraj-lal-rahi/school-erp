<?php

namespace App\Modules\SuperAdmin\Middleware;

use App\Modules\SuperAdmin\Models\PlatformAdmin;
use App\Services\Rbac\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function __construct(
        protected AccessControlService $accessControl,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if(! $user, 401, 'Unauthenticated.');

        if ($this->accessControl->isSuperAdmin($user)) {
            return $next($request);
        }

        $isPlatformAdmin = PlatformAdmin::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        abort_if(! $isPlatformAdmin, 403, 'Forbidden.');

        return $next($request);
    }
}
