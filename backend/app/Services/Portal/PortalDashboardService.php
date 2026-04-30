<?php

namespace App\Services\Portal;

use App\Models\User;

class PortalDashboardService
{
    public function __construct(
        protected PortalAuthContextService $contextService,
        protected PortalStudentDataService $studentData,
    ) {
    }

    public function build(User $user): array
    {
        $context = $this->contextService->resolve($user);
        $active = $context['active_context'];

        if (! $active || empty($active['active_student_id'])) {
            return [
                'mode' => $context['profile_mode'],
                'available_profiles' => $context['available_profiles'],
                'accessible_students' => $context['accessible_students'],
                'active_context' => $active,
                'dashboard' => null,
            ];
        }

        $studentId = (int) $active['active_student_id'];
        $overview = $this->studentData->overview($user, $studentId);

        return [
            'mode' => $active['active_profile_type'] === 'guardian' ? 'parent_dashboard' : 'student_dashboard',
            'available_profiles' => $context['available_profiles'],
            'accessible_students' => $context['accessible_students'],
            'active_context' => $active,
            'dashboard' => [
                'student' => $overview['student'],
                'kpis' => [
                    'attendance_percentage' => $overview['attendance']['summary']['percentage'] ?? null,
                    'pending_fees' => $overview['fees']['summary']['total_due'] ?? 0,
                    'latest_result_percentage' => $overview['results']['latest_result']['percentage'] ?? null,
                    'upcoming_classes' => count($overview['timetable']['upcoming'] ?? []),
                    'unread_announcements' => $overview['announcements']['unread_count'] ?? 0,
                ],
                'attendance' => $overview['attendance'],
                'fees' => $overview['fees'],
                'results' => [
                    'latest_result' => $overview['results']['latest_result'] ?? null,
                ],
                'timetable' => $overview['timetable'],
                'announcements' => $overview['announcements'],
                'permissions' => $overview['permissions'],
            ],
        ];
    }
}
