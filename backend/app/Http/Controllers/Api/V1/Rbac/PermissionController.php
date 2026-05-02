<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rbac\PermissionResource;
use App\Models\Permission;
use App\Services\Rbac\PermissionService;
use App\Services\Rbac\PermissionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissions,
        protected PermissionSyncService $syncService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = $this->permissions->paginate(
            $request->only(['search', 'module', 'action', 'status']),
            (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => PermissionResource::collection($permissions->getCollection()),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
            ],
        ]);
    }

    public function grouped(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Permission::class);

        $grouped = $this->permissions->groupedByModule(
            $request->only(['search', 'module', 'action', 'status']),
        );

        return response()->json([
            'data' => collect($grouped)->map(
                fn ($permissions, $module): array => [
                    'module' => $module,
                    'permissions' => PermissionResource::collection(collect($permissions)),
                ],
            )->values(),
        ]);
    }

    public function sync(): JsonResponse
    {
        $this->authorize('sync', Permission::class);

        return response()->json([
            'message' => 'Permissions synchronized successfully.',
            'data' => PermissionResource::collection($this->syncService->sync()),
        ]);
    }
}
