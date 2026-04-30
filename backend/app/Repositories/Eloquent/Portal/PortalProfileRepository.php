<?php

namespace App\Repositories\Eloquent\Portal;

use App\Models\Portal\PortalUserProfile;
use App\Repositories\Contracts\Portal\PortalProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PortalProfileRepository implements PortalProfileRepositoryInterface
{
    public function getByUser(int $userId): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('profile_type')
            ->orderByDesc('id')
            ->get();
    }

    public function findOrFail(int $id): PortalUserProfile
    {
        return $this->query()->findOrFail($id);
    }

    public function findByUserAndProfile(int $userId, string $profileType, int $profileId): ?PortalUserProfile
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('profile_type', $profileType)
            ->where('profile_id', $profileId)
            ->first();
    }

    public function create(array $attributes): PortalUserProfile
    {
        $portalUserProfile = PortalUserProfile::create($attributes);

        return $this->findOrFail($portalUserProfile->id);
    }

    public function update(PortalUserProfile $portalUserProfile, array $attributes): PortalUserProfile
    {
        $portalUserProfile->update($attributes);

        return $this->findOrFail($portalUserProfile->id);
    }

    public function delete(PortalUserProfile $portalUserProfile): void
    {
        $portalUserProfile->delete();
    }

    protected function query()
    {
        return PortalUserProfile::query()
            ->with(['user', 'studentProfile', 'guardianProfile']);
    }
}
