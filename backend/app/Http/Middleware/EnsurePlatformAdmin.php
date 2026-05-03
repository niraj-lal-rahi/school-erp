<?php

namespace App\Http\Middleware;

use App\Models\Platform\PlatformAdmin;
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

        $isPlatformAdmin = PlatformAdmin::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        abort_unless(
            $this->accessControl->isSuperAdmin($user) || $isPlatformAdmin,
            403,
            'Platform administrator access is required.'
        );

        return $next($request);
    }
}
