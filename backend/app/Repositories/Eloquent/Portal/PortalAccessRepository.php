<?php

namespace App\Repositories\Eloquent\Portal;

use App\Models\Portal\PortalProfileAccess;
use App\Models\Student;
use App\Repositories\Contracts\Portal\PortalAccessRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PortalAccessRepository implements PortalAccessRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['user_id'] ?? null, fn (Builder $query, int $value) => $query->where('user_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $value) => $query->where('student_id', $value))
            ->when($filters['guardian_id'] ?? null, fn (Builder $query, int $value) => $query->where('guardian_id', $value))
            ->when($filters['access_type'] ?? null, fn (Builder $query, string $value) => $query->where('access_type', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function getAccessibleStudents(int $userId): Collection
    {
        return Student::query()
            ->whereIn('students.id', function ($query) use ($userId): void {
                $query->select('student_id')
                    ->from('portal_profile_access')
                    ->where('user_id', $userId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at');
            })
            ->with(['guardians', 'enrollments'])
            ->orderBy('full_name')
            ->get();
    }

    public function getActiveAccessesByUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('student_id')
            ->get();
    }

    public function findOrFail(int $id): PortalProfileAccess
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): PortalProfileAccess
    {
        $portalProfileAccess = PortalProfileAccess::create($attributes);

        return $this->findOrFail($portalProfileAccess->id);
    }

    public function update(PortalProfileAccess $portalProfileAccess, array $attributes): PortalProfileAccess
    {
        $portalProfileAccess->update($attributes);

        return $this->findOrFail($portalProfileAccess->id);
    }

    public function delete(PortalProfileAccess $portalProfileAccess): void
    {
        $portalProfileAccess->delete();
    }

    protected function query(): Builder
    {
        return PortalProfileAccess::query()
            ->with(['user', 'student.guardians', 'guardian']);
    }
}
