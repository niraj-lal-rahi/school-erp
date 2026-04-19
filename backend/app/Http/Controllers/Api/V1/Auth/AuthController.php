<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DataTransferObjects\Auth\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RefreshTokenRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'Login successful.',
            'data' => $this->authService->login(LoginData::fromArray($request->validated())),
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
