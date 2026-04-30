<?php

namespace App\Services\Portal;

use App\Models\Guardian;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Portal\PortalUserProfile;
use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\Portal\PortalAccessRepositoryInterface;
use App\Repositories\Contracts\Portal\PortalProfileRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PortalProfileService
{
    public function __construct(
        protected PortalProfileRepositoryInterface $profiles,
        protected PortalAccessRepositoryInterface $accesses,
    ) {
    }

    public function linkStudentProfile(User $user, Student $student, array $attributes = []): PortalUserProfile
    {
        $this->assertSameTenant($user->school_id, $student->school_id);

        if ($student->user_id && (int) $student->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'student_id' => 'This student is already linked to another user account.',
            ]);
        }

        return DB::transaction(function () use ($user, $student, $attributes): PortalUserProfile {
            $existing = $this->profiles->findByUserAndProfile($user->id, 'student', $student->id);

            if ($attributes['is_default'] ?? false) {
                $this->clearDefaultProfiles($user);
            }

            $profile = $existing
                ? $this->profiles->update($existing, [
                    'is_default' => (bool) ($attributes['is_default'] ?? $existing->is_default),
                    'status' => $attributes['status'] ?? $existing->status,
                ])
                : $this->profiles->create([
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                    'profile_type' => 'student',
                    'profile_id' => $student->id,
                    'is_default' => (bool) ($attributes['is_default'] ?? false),
                    'status' => $attributes['status'] ?? 'active',
                ]);

            $student->update(['user_id' => $user->id]);

            $this->upsertAccess([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'guardian_id' => null,
                'access_type' => 'self',
                'status' => 'active',
            ]);

            return $profile;
        });
    }

    public function linkGuardianProfile(User $user, Guardian $guardian, array $attributes = []): PortalUserProfile
    {
        $this->assertSameTenant($user->school_id, $guardian->school_id);

        return DB::transaction(function () use ($user, $guardian, $attributes): PortalUserProfile {
            $existing = $this->profiles->findByUserAndProfile($user->id, 'guardian', $guardian->id);

            if ($attributes['is_default'] ?? false) {
                $this->clearDefaultProfiles($user);
            }

            $profile = $existing
                ? $this->profiles->update($existing, [
                    'is_default' => (bool) ($attributes['is_default'] ?? $existing->is_default),
                    'status' => $attributes['status'] ?? $existing->status,
                ])
                : $this->profiles->create([
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                    'profile_type' => 'guardian',
                    'profile_id' => $guardian->id,
                    'is_default' => (bool) ($attributes['is_default'] ?? false),
                    'status' => $attributes['status'] ?? 'active',
                ]);

            $this->syncGuardianStudentAccess($user, $guardian);

            return $profile;
        });
    }

    public function syncGuardianStudentAccess(User $user, Guardian $guardian): Collection
    {
        $this->assertSameTenant($user->school_id, $guardian->school_id);

        $guardian->loadMissing('students');

        return DB::transaction(function () use ($user, $guardian): Collection {
            $rows = collect();

            foreach ($guardian->students as $student) {
                $relationship = strtolower((string) ($student->pivot->relationship_label ?? $student->pivot->relationship ?? $guardian->relationship_type ?? 'guardian'));

                $rows->push($this->upsertAccess([
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                    'student_id' => $student->id,
                    'guardian_id' => $guardian->id,
                    'access_type' => str_contains($relationship, 'guardian') ? 'guardian' : 'parent',
                    'status' => 'active',
                ]));
            }

            return $rows;
        });
    }

    public function updateAccess(PortalProfileAccess $access, array $attributes): PortalProfileAccess
    {
        return $this->accesses->update($access, $attributes);
    }

    protected function upsertAccess(array $attributes): PortalProfileAccess
    {
        $existing = PortalProfileAccess::query()
            ->where('user_id', $attributes['user_id'])
            ->where('student_id', $attributes['student_id'])
            ->where('access_type', $attributes['access_type'])
            ->first();

        if ($existing) {
            return $this->accesses->update($existing, array_merge($attributes, [
                'can_view_attendance' => $attributes['can_view_attendance'] ?? true,
                'can_view_fees' => $attributes['can_view_fees'] ?? true,
                'can_pay_fees' => $attributes['can_pay_fees'] ?? true,
                'can_view_results' => $attributes['can_view_results'] ?? true,
                'can_view_documents' => $attributes['can_view_documents'] ?? true,
                'can_message_teacher' => $attributes['can_message_teacher'] ?? true,
            ]));
        }

        return $this->accesses->create(array_merge([
            'can_view_attendance' => true,
            'can_view_fees' => true,
            'can_pay_fees' => true,
            'can_view_results' => true,
            'can_view_documents' => true,
            'can_message_teacher' => true,
        ], $attributes));
    }

    protected function clearDefaultProfiles(User $user): void
    {
        $this->profiles->getByUser($user->id)->each(function (PortalUserProfile $profile): void {
            if ($profile->is_default) {
                $this->profiles->update($profile, ['is_default' => false]);
            }
        });
    }

    protected function assertSameTenant(int $userSchoolId, int $entitySchoolId): void
    {
        if ($userSchoolId !== $entitySchoolId) {
            throw ValidationException::withMessages([
                'profile' => 'Cross-tenant portal profile linking is not allowed.',
            ]);
        }
    }
}
