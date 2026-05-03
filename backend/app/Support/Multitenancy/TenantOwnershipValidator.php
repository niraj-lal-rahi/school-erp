<?php

namespace App\Support\Multitenancy;

use App\Exceptions\CrossTenantAccessException;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TenantOwnershipValidator
{
    public function assertUserOwnsModel(User $user, Model $model, string $schoolColumn = 'school_id', ?string $message = null): void
    {
        $this->assertSameTenant(
            (int) $user->school_id,
            (int) ($model->getAttribute($schoolColumn) ?? 0),
            $message ?? 'You cannot access records from another tenant.'
        );
    }

    public function assertRequestedSchool(User $user, ?int $schoolId, ?string $message = null): void
    {
        if ($schoolId === null) {
            return;
        }

        $this->assertSameTenant(
            (int) $user->school_id,
            (int) $schoolId,
            $message ?? 'Requested tenant context does not belong to the authenticated user.'
        );
    }

    public function assertSameTenant(int $expectedSchoolId, int $actualSchoolId, string $message = 'Cross-tenant access is not allowed.'): void
    {
        if ($expectedSchoolId !== $actualSchoolId) {
            throw new CrossTenantAccessException($message);
        }
    }
}
