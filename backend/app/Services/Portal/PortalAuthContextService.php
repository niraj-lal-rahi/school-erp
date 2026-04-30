<?php

namespace App\Services\Portal;

use App\Models\Portal\PortalSession;
use App\Models\User;
use App\Repositories\Contracts\Portal\PortalAccessRepositoryInterface;
use App\Repositories\Contracts\Portal\PortalProfileRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortalAuthContextService
{
    public function __construct(
        protected PortalProfileRepositoryInterface $profiles,
        protected PortalAccessRepositoryInterface $accesses,
        protected PortalActivityLogService $activityLogs,
    ) {
    }

    public function resolve(User $user): array
    {
        $profiles = $this->profiles->getByUser($user->id);
        $session = $this->currentSession($user);

        if (! $session && $profiles->isNotEmpty()) {
            $session = $this->bootstrapSession($user, $profiles);
        }

        $activeContext = $session ? $this->buildActiveContext($session) : null;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'profile_mode' => $this->determineMode($profiles),
            'available_profiles' => $this->formatProfiles($profiles),
            'accessible_students' => $this->formatAccessibleStudents($user),
            'active_context' => $activeContext,
        ];
    }

    public function availableProfiles(User $user): Collection
    {
        return $this->profiles->getByUser($user->id);
    }

    public function accessibleStudents(User $user): Collection
    {
        return $this->accesses->getAccessibleStudents($user->id);
    }

    public function currentSession(User $user): ?PortalSession
    {
        return PortalSession::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    public function switchContext(User $user, array $payload, array $requestContext = []): PortalSession
    {
        $profiles = $this->profiles->getByUser($user->id);
        $profile = $profiles->first(function ($row) use ($payload) {
            return $row->profile_type === $payload['active_profile_type']
                && (int) $row->profile_id === (int) $payload['active_profile_id']
                && $row->status === 'active';
        });

        if (! $profile) {
            throw ValidationException::withMessages([
                'active_profile_id' => 'The selected portal profile is not linked to the current user.',
            ]);
        }

        if ($payload['active_profile_type'] === 'guardian' && empty($payload['active_student_id'])) {
            throw ValidationException::withMessages([
                'active_student_id' => 'A guardian context requires an active child selection.',
            ]);
        }

        if ($payload['active_profile_type'] === 'student') {
            $payload['active_student_id'] = (int) $payload['active_profile_id'];
        }

        if (! empty($payload['active_student_id'])) {
            $hasAccess = $this->accesses->getActiveAccessesByUser($user->id)
                ->contains(fn ($access) => (int) $access->student_id === (int) $payload['active_student_id']);

            if (! $hasAccess) {
                throw ValidationException::withMessages([
                    'active_student_id' => 'The selected student is not accessible in the current portal context.',
                ]);
            }
        }

        $session = DB::transaction(function () use ($user, $payload): PortalSession {
            $existing = $this->currentSession($user);

            if ($existing) {
                $existing->update([
                    'active_profile_type' => $payload['active_profile_type'],
                    'active_profile_id' => $payload['active_profile_id'],
                    'active_student_id' => $payload['active_student_id'] ?? null,
                    'last_seen_at' => now(),
                ]);

                return $existing->fresh(['activeStudent', 'activeStudentProfile', 'activeGuardianProfile']);
            }

            return PortalSession::query()->create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'active_profile_type' => $payload['active_profile_type'],
                'active_profile_id' => $payload['active_profile_id'],
                'active_student_id' => $payload['active_student_id'] ?? null,
                'last_seen_at' => now(),
                'device_info' => $requestContext['device_info'] ?? null,
                'ip_address' => $requestContext['ip_address'] ?? null,
            ])->fresh(['activeStudent', 'activeStudentProfile', 'activeGuardianProfile']);
        });

        $this->activityLogs->log($user, 'portal.context_switched', [
            'student_id' => $session->active_student_id,
            'description' => 'Portal context switched.',
            'metadata' => [
                'active_profile_type' => $session->active_profile_type,
                'active_profile_id' => $session->active_profile_id,
            ],
            'ip_address' => $requestContext['ip_address'] ?? null,
            'user_agent' => $requestContext['user_agent'] ?? null,
        ]);

        return $session;
    }

    protected function bootstrapSession(User $user, Collection $profiles): PortalSession
    {
        $defaultProfile = $profiles->firstWhere('is_default', true) ?: $profiles->first();
        $activeStudentId = $defaultProfile->profile_type === 'student'
            ? (int) $defaultProfile->profile_id
            : (int) optional(
                $this->accesses->getActiveAccessesByUser($user->id)->firstWhere('guardian_id', $defaultProfile->profile_id)
            )->student_id;

        return PortalSession::query()->create([
            'school_id' => $user->school_id,
            'user_id' => $user->id,
            'active_profile_type' => $defaultProfile->profile_type,
            'active_profile_id' => $defaultProfile->profile_id,
            'active_student_id' => $activeStudentId ?: null,
            'last_seen_at' => now(),
        ])->fresh(['activeStudent', 'activeStudentProfile', 'activeGuardianProfile']);
    }

    protected function determineMode(Collection $profiles): string
    {
        $types = $profiles->pluck('profile_type')->unique()->values();

        if ($types->count() > 1) {
            return 'multi_role';
        }

        return $types->first() ?? 'none';
    }

    protected function buildActiveContext(PortalSession $session): array
    {
        return [
            'active_profile_type' => $session->active_profile_type,
            'active_profile_id' => $session->active_profile_id,
            'active_profile' => $this->formatProfileEntity($session->active_profile_type, $session->activeProfile),
            'active_student_id' => $session->active_student_id,
            'active_student' => $session->activeStudent ? [
                'id' => $session->activeStudent->id,
                'full_name' => $session->activeStudent->full_name,
                'admission_no' => $session->activeStudent->admission_no,
                'roll_no' => $session->activeStudent->roll_no,
            ] : null,
            'last_seen_at' => optional($session->last_seen_at)->toIso8601String(),
        ];
    }

    protected function formatProfiles(Collection $profiles): array
    {
        return $profiles->map(function ($profile): array {
            return [
                'id' => $profile->id,
                'profile_type' => $profile->profile_type,
                'profile_id' => $profile->profile_id,
                'is_default' => (bool) $profile->is_default,
                'status' => $profile->status,
                'profile' => $this->formatProfileEntity($profile->profile_type, $profile->profile),
            ];
        })->values()->all();
    }

    protected function formatAccessibleStudents(User $user): array
    {
        return $this->accesses->getAccessibleStudents($user->id)
            ->map(fn ($student) => [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'roll_no' => $student->roll_no,
                'current_enrollment' => optional($student->enrollments->firstWhere('is_current', true), function ($enrollment) {
                    return [
                        'academic_year_id' => $enrollment->academic_year_id,
                        'class_id' => $enrollment->school_class_id,
                        'section_id' => $enrollment->section_id,
                    ];
                }),
            ])
            ->values()
            ->all();
    }

    protected function formatProfileEntity(string $profileType, $profile): ?array
    {
        if (! $profile) {
            return null;
        }

        return match ($profileType) {
            'student' => [
                'id' => $profile->id,
                'full_name' => $profile->full_name,
                'admission_no' => $profile->admission_no,
                'roll_no' => $profile->roll_no,
                'email' => $profile->email,
                'phone' => $profile->phone,
            ],
            'guardian' => [
                'id' => $profile->id,
                'full_name' => $profile->full_name,
                'relationship_type' => $profile->relationship_type,
                'email' => $profile->email,
                'phone' => $profile->phone,
            ],
            default => null,
        };
    }
}
