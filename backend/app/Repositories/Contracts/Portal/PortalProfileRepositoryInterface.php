<?php

namespace App\Repositories\Contracts\Portal;

use App\Models\Portal\PortalUserProfile;
use Illuminate\Database\Eloquent\Collection;

interface PortalProfileRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function findOrFail(int $id): PortalUserProfile;

    public function findByUserAndProfile(int $userId, string $profileType, int $profileId): ?PortalUserProfile;

    public function create(array $attributes): PortalUserProfile;

    public function update(PortalUserProfile $portalUserProfile, array $attributes): PortalUserProfile;

    public function delete(PortalUserProfile $portalUserProfile): void;
}
