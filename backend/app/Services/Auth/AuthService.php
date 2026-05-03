<?php

namespace App\Services\Auth;

use App\DataTransferObjects\Auth\LoginData;
use App\Events\Auth\UserLoggedIn;
use App\Models\Platform\PlatformAdmin;
use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Rbac\AccessControlService;
use App\Support\Auth\JwtManager;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected SchoolRepositoryInterface $schools,
        protected UserRepositoryInterface $users,
        protected JwtManager $jwtManager,
        protected TenantContext $tenantContext,
        protected AccessControlService $accessControl,
    ) {
    }

    public function login(LoginData $data): array
    {
        $school = $this->schools->findByIdentifier($data->tenantCode);
        abort_if(! $school, 422, 'Unknown tenant.');

        $this->tenantContext->set($school);

        $user = $this->users->findForLogin($school->id, $data->email);

        abort_if(! $user || ! Hash::check($data->password, $user->password), 401, 'Invalid credentials.');

        event(new UserLoggedIn($user));

        return [
            'access_token' => $this->jwtManager->issueAccessToken($user),
            'refresh_token' => $this->jwtManager->issueRefreshToken($user),
            'user' => $user->load('roles.permissions'),
            'tenant' => $school,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('erp.jwt.ttl', 60) * 60,
        ];
    }

    public function platformLogin(string $email, string $password): array
    {
        $this->tenantContext->clear();

        $user = $this->users->findPlatformUserForLogin($email);

        abort_if(! $user || ! Hash::check($password, $user->password), 401, 'Invalid credentials.');

        $hasPlatformAccess = $this->accessControl->isSuperAdmin($user)
            || PlatformAdmin::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

        abort_if(! $hasPlatformAccess, 403, 'Platform access is not enabled for this account.');

        event(new UserLoggedIn($user));

        return [
            'access_token' => $this->jwtManager->issueAccessToken($user),
            'refresh_token' => $this->jwtManager->issueRefreshToken($user),
            'user' => $user->load('roles.permissions'),
            'tenant' => null,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('erp.jwt.ttl', 60) * 60,
        ];
    }

    public function refresh(string $refreshToken): array
    {
        $response = $this->jwtManager->refresh($refreshToken);

        return [
            ...$response,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('erp.jwt.ttl', 60) * 60,
        ];
    }

    public function logout($user): void
    {
        $this->jwtManager->revokeAllForUser($user);
    }
}
