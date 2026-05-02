<?php

namespace App\Http\Controllers\Api\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Resources\Rbac\UserRoleResource;
use App\Http\Requests\Rbac\AssignUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Rbac\AccessControlService;
use App\Services\Rbac\RoleAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserRoleController extends Controller
{
    public function __construct(
        protected RoleAssignmentService $assignments,
        protected AccessControlService $accessControl,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);
        $this->authorize('viewAny', UserRole::class);
        $this->guardTargetUser($request->user(), $user);

        return response()->json([
            'data' => UserRoleResource::collection($this->assignments->listUserRoles($user)),
        ]);
    }

    public function store(AssignUserRoleRequest $request, int $id): JsonResponse
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);
        $this->authorize('create', UserRole::class);
        $this->guardTargetUser($request->user(), $user);
        $role = Role::withoutGlobalScopes()->findOrFail($request->integer('role_id'));

        $assignment = $this->assignments->assignRole($user, $role, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Role assigned to user successfully.',
            'data' => new UserRoleResource($assignment),
        ], 201);
    }

    public function destroy(Request $request, int $id, int $roleId): JsonResponse
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);
        $this->guardTargetUser($request->user(), $user);
        $role = Role::withoutGlobalScopes()->findOrFail($roleId);
        $existing = UserRole::query()
            ->where('role_id', $role->id)
            ->where('user_id', $user->id)
            ->where(function ($query) use ($user): void {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            })
            ->first();

        if ($existing) {
            $this->authorize('delete', $existing);
        } else {
            $this->authorize('delete', new UserRole([
                'school_id' => $role->school_id ?? $user->school_id,
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]));
        }

        $this->assignments->removeRole($user, $role, $request->user(), $request->ip());

        return response()->json(null, 204);
    }

    protected function guardTargetUser(User $performedBy, User $targetUser): void
    {
        if ($this->accessControl->isSuperAdmin($performedBy)) {
            return;
        }

        if ($performedBy->id === $targetUser->id) {
            return;
        }

        if ($performedBy->school_id !== $targetUser->school_id) {
            throw ValidationException::withMessages([
                'user_id' => ['You cannot manage roles for a user from another tenant.'],
            ]);
        }
    }
}
