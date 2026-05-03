<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DataTransferObjects\Auth\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PlatformLoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Models\Platform\PlatformAdmin;
use App\Services\Auth\AuthService;
use App\Services\Platform\PlatformAuditService;
use App\Services\Rbac\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected AccessControlService $accessControl,
        protected PlatformAuditService $platformAudit,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login(LoginData::fromArray($request->validated()));
        $user = $payload['user'] ?? null;

        if ($user && ($this->accessControl->isSuperAdmin($user) || PlatformAdmin::query()->where('user_id', $user->id)->where('status', 'active')->exists())) {
            $this->platformAudit->logSuperAdminLogin($user, [
                'tenant_code' => $request->validated('tenant_code'),
            ], $request);
        }

        return response()->json([
            'message' => 'Login successful.',
            'data' => $payload,
        ]);
    }

    public function platformLogin(PlatformLoginRequest $request): JsonResponse
    {
        $payload = $this->authService->platformLogin(
            $request->validated('email'),
            $request->validated('password'),
        );

        $user = $payload['user'] ?? null;

        if ($user) {
            $this->platformAudit->logSuperAdminLogin($user, [
                'auth_surface' => 'platform',
            ], $request);
        }

        return response()->json([
            'message' => 'Platform login successful.',
            'data' => $payload,
        ]);
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Token refreshed successfully.',
            'data' => $this->authService->refresh($request->validated('refresh_token')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->load('roles.permissions', 'school'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
