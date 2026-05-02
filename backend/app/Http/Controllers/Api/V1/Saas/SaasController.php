<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Controllers\Controller;
use App\Models\Saas\Tenant;
use App\Services\Rbac\AccessControlService;
use Illuminate\Http\Request;

abstract class SaasController extends Controller
{
    protected function ensureSuperAdmin(Request $request): void
    {
        abort_unless(
            app(AccessControlService::class)->isSuperAdmin($request->user()),
            403,
            'Only a super admin can perform this action.'
        );
    }

    protected function ensureTenantViewerAccess(Request $request, Tenant $tenant): void
    {
        $user = $request->user();
        $access = app(AccessControlService::class);

        if ($access->isSuperAdmin($user)) {
            return;
        }

        abort_if((int) $user->school_id !== (int) $tenant->id, 403, 'You can only access your own tenant.');
        abort_unless($access->checkRole($user, 'tenant_admin'), 403, 'Only a tenant admin can access this tenant.');
    }
}
