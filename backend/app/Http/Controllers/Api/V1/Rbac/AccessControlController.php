<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rbac\PermissionResource;
use App\Http\Resources\Rbac\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Rbac\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccessControlController extends Controller
{
    public function __construct(
        protected AccessControlService $accessControl,
    ) {
    }

    public function myPermissions(Request $request): JsonResponse
    {
        $this->authorize('viewOwn', Permission::class);

        return response()->json([
            'data' => PermissionResource::collection($this->accessControl->resolvePermissions($request->user())),
        ]);
    }

    public function myRoles(Request $request): JsonResponse
    {
        $this->authorize('viewOwn', Role::class);

        return response()->json([
            'data' => RoleResource::collection($this->accessControl->resolveRoles($request->user())),
        ]);
    }

    public function checkPermission(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'permission_code' => ['required', 'string'],
        ]);

        return response()->json([
            'data' => [
                'permission_code' => $validated['permission_code'],
                'allowed' => $this->accessControl->checkPermission($request->user(), $validated['permission_code']),
            ],
        ]);
    }
}
