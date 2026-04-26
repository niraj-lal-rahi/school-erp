<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\CommunicationGroup;
use App\Repositories\Contracts\Communication\CommunicationGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CommunicationGroupRepository implements CommunicationGroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $groupQuery) use ($search): void {
                    $groupQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['class_id'] ?? null, fn (Builder $query, $value) => $query->where('class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, $value) => $query->where('section_id', $value))
            ->when($filters['recipient_type'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('members', fn (Builder $memberQuery) => $memberQuery->where('member_type', $value));
            })
            ->when($filters['recipient_id'] ?? null, function (Builder $query, $value): void {
                $query->whereHas('members', fn (Builder $memberQuery) => $memberQuery->where('member_id', $value));
            })
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): CommunicationGroup
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): CommunicationGroup
    {
        $group = CommunicationGroup::create($attributes);

        return $this->findOrFail($group->id);
    }

    public function update(CommunicationGroup $group, array $attributes): CommunicationGroup
    {
        $group->update($attributes);

        return $this->findOrFail($group->id);
    }

    public function delete(CommunicationGroup $group): void
    {
        $group->delete();
    }

    protected function query(): Builder
    {
        return CommunicationGroup::query()
            ->with([
                'schoolClass',
                'section',
            ])
            ->withCount('members');
    }
}
