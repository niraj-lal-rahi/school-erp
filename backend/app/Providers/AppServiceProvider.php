<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\SchoolRepository;
use App\Repositories\Eloquent\StudentRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Support\Auth\JwtManager;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(JwtManager::class);

        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('jwt', function ($request): ?User {
            $token = $request->bearerToken();

            if (! $token) {
                return null;
            }

            $payload = app(JwtManager::class)->decode($token);

            /** @var User|null $user */
            $user = User::query()
                ->withoutGlobalScopes()
                ->find($payload['sub'] ?? null);

            if (! $user) {
                return null;
            }

            if (($payload['school_id'] ?? null) && $user->school_id !== $payload['school_id']) {
                return null;
            }

            app(TenantContext::class)->set($user->school);

            return $user;
        });

        Gate::policy(Student::class, StudentPolicy::class);
    }
}
