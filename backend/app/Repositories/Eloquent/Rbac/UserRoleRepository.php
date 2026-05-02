<?php

namespace App\Repositories\Eloquent\Rbac;

use App\Models\UserRole;
use App\Repositories\Contracts\Rbac\UserRoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($filters)
            ->latest('id')
            ->paginate($perPage);
    }

    public function forUser(int $userId, ?int $schoolId = null): Collection
    {
        return UserRole::query()
            ->where('user_id', $userId)
            ->when($schoolId !== null, function (Builder $query) use ($schoolId): void {
                $query->where(function (Builder $scopeQuery) use ($schoolId): void {
                    $scopeQuery->whereNull('school_id')
                        ->orWhere('school_id', $schoolId);
                });
            })
            ->with(['role', 'assignedBy'])
            ->latest('id')
            ->get();
    }

    public function findByUserAndRole(int $userId, int $roleId, ?int $schoolId = null): ?UserRole
    {
        return UserRole::query()
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->when($schoolId !== null, function (Builder $query) use ($schoolId): void {
                $query->where(function (Builder $scopeQuery) use ($schoolId): void {
                    $scopeQuery->whereNull('school_id')
                        ->orWhere('school_id', $schoolId);
                });
            })
            ->first();
    }

    public function create(array $attributes): UserRole
    {
        $userRole = UserRole::query()->create($attributes);

        return UserRole::query()->with(['role', 'assignedBy'])->findOrFail($userRole->id);
    }

    public function delete(UserRole $userRole): void
    {
        $userRole->delete();
    }

    protected function query(array $filters = []): Builder
    {
        $schoolId = auth()->user()?->school_id;

        return UserRole::query()
            ->with(['user', 'role', 'assignedBy'])
            ->when($schoolId !== null, function (Builder $query) use ($schoolId): void {
                $query->where(function (Builder $scopeQuery) use ($schoolId): void {
                    $scopeQuery->whereNull('user_roles.school_id')
                        ->orWhere('user_roles.school_id', $schoolId);
                });
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereHas('user', function (Builder $userQuery) use ($search): void {
                    $userQuery->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role_type'] ?? null, fn (Builder $query, string $value) => $query->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('role_type', $value)))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->whereHas('role', fn (Builder $roleQuery) => $roleQuery->where('status', $value)));
    }
}
