<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Resources\Portal\PortalStudentOverviewResource;
use App\Models\Portal\PortalProfileAccess;
use App\Services\Portal\PortalActivityLogService;
use App\Services\Portal\PortalStudentDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalStudentController extends Controller
{
    public function __construct(
        protected PortalStudentDataService $studentData,
        protected PortalActivityLogService $activityLogs,
    ) {
    }

    public function overview(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewStudent', [PortalProfileAccess::class, $studentId]);

        $data = $this->studentData->overview($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_overview_viewed', 'Portal student overview viewed.');

        return response()->json(['data' => new PortalStudentOverviewResource($data)]);
    }

    public function attendance(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewAttendance', [PortalProfileAccess::class, $studentId]);

        $data = $this->studentData->attendance($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_attendance_viewed', 'Portal attendance viewed.');

        return response()->json(['data' => $data]);
    }

    public function fees(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewFees', [PortalProfileAccess::class, $studentId]);

        $data = $this->studentData->fees($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_fees_viewed', 'Portal fees viewed.');

        return response()->json(['data' => $data]);
    }

    public function results(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewResults', [PortalProfileAccess::class, $studentId]);

        $data = $this->studentData->results($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_results_viewed', 'Portal results viewed.');

        return response()->json(['data' => $data]);
    }

    public function timetable(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewTimetable', [PortalProfileAccess::class, $studentId]);

        $this->studentData->assertAccess($request->user(), $studentId);
        $data = $this->studentData->timetable($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_timetable_viewed', 'Portal timetable viewed.');

        return response()->json(['data' => $data]);
    }

    public function assignments(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewAssignments', [PortalProfileAccess::class, $studentId]);

        $this->studentData->assertAccess($request->user(), $studentId);
        $data = $this->studentData->assignments($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_assignments_viewed', 'Portal assignments viewed.');

        return response()->json(['data' => $data]);
    }

    public function transport(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewTransport', [PortalProfileAccess::class, $studentId]);

        $this->studentData->assertAccess($request->user(), $studentId);
        $data = $this->studentData->transport($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_transport_viewed', 'Portal transport viewed.');

        return response()->json(['data' => $data]);
    }

    public function documents(Request $request, int $studentId): JsonResponse
    {
        $this->authorize('viewDocuments', [PortalProfileAccess::class, $studentId]);

        $data = $this->studentData->documents($request->user(), $studentId);
        $this->logStudentView($request, $studentId, 'portal.student_documents_viewed', 'Portal documents viewed.');

        return response()->json(['data' => $data]);
    }

    protected function logStudentView(Request $request, int $studentId, string $action, string $description): void
    {
        $this->activityLogs->log($request->user(), $action, [
            'student_id' => $studentId,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
