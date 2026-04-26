<?php

namespace App\Services\Communication;

use App\Models\Communication\CommunicationGroup;
use App\Models\Communication\CommunicationGroupMember;
use App\Repositories\Contracts\Communication\CommunicationGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommunicationGroupService
{
    public function __construct(
        protected CommunicationGroupRepositoryInterface $groups,
        protected RecipientResolverService $recipientResolver,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->groups->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): CommunicationGroup
    {
        return $this->groups->findOrFail($id);
    }

    public function create(array $attributes): CommunicationGroup
    {
        return DB::transaction(function () use ($attributes): CommunicationGroup {
            $group = $this->groups->create($attributes);

            if (in_array($group->group_type, ['class', 'section'], true)) {
                $this->syncClassSectionGroupMembers($group);
            }

            return $this->groups->findOrFail($group->id);
        });
    }

    public function update(CommunicationGroup $group, array $attributes): CommunicationGroup
    {
        return DB::transaction(function () use ($group, $attributes): CommunicationGroup {
            $group = $this->groups->update($group, $attributes);

            if (in_array($group->group_type, ['class', 'section'], true)) {
                $this->syncClassSectionGroupMembers($group);
            }

            return $this->groups->findOrFail($group->id);
        });
    }

    public function delete(CommunicationGroup $group): void
    {
        DB::transaction(function () use ($group): void {
            $this->groups->delete($group);
        });
    }

    public function addMember(CommunicationGroup $group, array $attributes): CommunicationGroupMember
    {
        return DB::transaction(fn (): CommunicationGroupMember => $group->members()->create([
            'school_id' => $group->school_id,
            'member_type' => $attributes['member_type'],
            'member_id' => $attributes['member_id'],
            'joined_at' => $attributes['joined_at'] ?? now(),
        ]));
    }

    public function removeMember(CommunicationGroup $group, int $memberId): void
    {
        DB::transaction(function () use ($group, $memberId): void {
            $group->members()->whereKey($memberId)->delete();
        });
    }

    public function syncClassSectionGroupMembers(CommunicationGroup $group): void
    {
        DB::transaction(function () use ($group): void {
            $recipients = $this->recipientResolver->resolveGroupMembers($group);

            $group->members()->delete();

            foreach ($recipients as $recipient) {
                $group->members()->create([
                    'school_id' => $group->school_id,
                    'member_type' => $recipient['recipient_type'],
                    'member_id' => $recipient['recipient_id'],
                    'joined_at' => now(),
                ]);
            }
        });
    }
}
