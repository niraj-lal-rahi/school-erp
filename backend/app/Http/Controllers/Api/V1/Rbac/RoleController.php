<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rbac\RoleResource;
use App\Http\Requests\Rbac\AssignPermissionsRequest;
use App\Http\Requests\Rbac\CloneRoleRequest;
use App\Http\Requests\Rbac\StoreRoleRequest;
use App\Http\Requests\Rbac\UpdateRoleRequest;
use App\Models\Role;
use App\Services\Rbac\RolePermissionService;
use App\Services\Rbac\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $roles,
        protected RolePermissionService $rolePermissions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        $roles = $this->roles->paginate(
            $request->only(['search', 'role_type', 'status']),
            (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => RoleResource::collection($roles->getCollection()),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ],
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $this->authorize('create', Role::class);

        $role = $this->roles->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ], $request->user(), $request->ip());

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function update(UpdateRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roles->findOrFail($id);
        $this->authorize('update', $role);
        $role = $this->roles->update($role, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => new RoleResource($role),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $role = $this->roles->findOrFail($id);
        $this->authorize('delete', $role);
        $this->roles->delete($role, $request->user(), $request->ip());

        return response()->json(null, 204);
    }

    public function syncPermissions(AssignPermissionsRequest $request, int $id): JsonResponse
    {
        $role = $this->roles->findOrFail($id);
        $this->authorize('assignPermissions', $role);
        $role = $this->rolePermissions->syncPermissions(
            $role,
            $request->validated('permission_ids'),
            $request->user(),
            $request->ip(),
        );

        return response()->json([
            'message' => 'Role permissions updated successfully.',
            'data' => new RoleResource($role),
        ]);
    }

    public function clone(CloneRoleRequest $request, int $id): JsonResponse
    {
        $role = $this->roles->findOrFail($id);
        $this->authorize('clone', $role);
        $cloned = $this->roles->clone($role, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Role cloned successfully.',
            'data' => new RoleResource($cloned),
        ], 201);
    }
}
