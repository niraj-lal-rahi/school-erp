<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceSessionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\BulkMarkStudentAttendanceRequest;
use App\Http\Requests\Attendance\MarkStudentAttendanceRequest;
use App\Http\Requests\Attendance\UpsertStudentAttendanceSessionRequest;
use App\Http\Resources\Attendance\StudentAttendanceSessionResource;
use App\Models\Attendance\StudentAttendanceSession;
use App\Services\Attendance\StudentAttendanceSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceSessionController extends Controller
{
    public function __construct(
        protected StudentAttendanceSessionService $sessions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentAttendanceSession::class);

        return response()->json([
            'data' => StudentAttendanceSessionResource::collection(
                $this->sessions->all($request->only([
                    'academic_year_id',
                    'class_id',
                    'section_id',
                    'staff_id',
                    'subject_id',
                    'status',
                    'session_type',
                    'attendance_date_from',
                    'attendance_date_to',
                ]))
            ),
        ]);
    }

    public function store(UpsertStudentAttendanceSessionRequest $request): JsonResponse
    {
        $session = $this->sessions->create(StudentAttendanceSessionData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'status' => $request->validated('status', 'draft'),
            'marked_by' => $request->user()->id,
            'submitted_at' => null,
            'locked_at' => null,
        ]));

        return response()->json([
            'message' => 'Attendance session created successfully.',
            'data' => new StudentAttendanceSessionResource($session),
        ], 201);
    }

    public function show(StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('view', $studentSession);

        return response()->json([
            'data' => new StudentAttendanceSessionResource($studentSession->load(['academicYear', 'schoolClass', 'section', 'period', 'subject', 'teacher', 'marker', 'records.student', 'records.attendanceStatus'])),
        ]);
    }

    public function update(UpsertStudentAttendanceSessionRequest $request, StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('update', $studentSession);

        $studentSession = $this->sessions->update($studentSession, StudentAttendanceSessionData::fromArray([
            ...$request->validated(),
            'school_id' => $studentSession->school_id,
            'status' => $studentSession->status,
            'marked_by' => $studentSession->marked_by,
            'submitted_at' => $studentSession->submitted_at,
            'locked_at' => $studentSession->locked_at,
        ]));

        return response()->json([
            'message' => 'Attendance session updated successfully.',
            'data' => new StudentAttendanceSessionResource($studentSession),
        ]);
    }

    public function destroy(StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('delete', $studentSession);
        $this->sessions->delete($studentSession);

        return response()->json(null, 204);
    }

    public function bulkMark(BulkMarkStudentAttendanceRequest $request, StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('update', $studentSession);

        $studentSession = $this->sessions->bulkMark($studentSession, $request->validated('records'), $request->user()->id);

        return response()->json([
            'message' => 'Attendance records saved successfully.',
            'data' => new StudentAttendanceSessionResource($studentSession),
        ]);
    }

    public function markForStudent(MarkStudentAttendanceRequest $request, int $studentId): JsonResponse
    {
        $studentSession = StudentAttendanceSession::query()->findOrFail($request->input('attendance_session_id'));
        $this->authorize('update', $studentSession);

        $studentSession = $this->sessions->bulkMark($studentSession, [[
            'student_id' => $studentId,
            'attendance_status_type_id' => $request->integer('attendance_status_type_id'),
            'check_in_time' => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
            'remarks' => $request->input('remarks'),
        ]], $request->user()->id);

        return response()->json([
            'message' => 'Student attendance updated successfully.',
            'data' => new StudentAttendanceSessionResource($studentSession),
        ]);
    }

    public function submit(StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('update', $studentSession);

        $studentSession = $this->sessions->submit($studentSession);

        return response()->json([
            'message' => 'Attendance session submitted successfully.',
            'data' => new StudentAttendanceSessionResource($studentSession),
        ]);
    }

    public function lock(StudentAttendanceSession $studentSession): JsonResponse
    {
        $this->authorize('update', $studentSession);

        $studentSession = $this->sessions->lock($studentSession);

        return response()->json([
            'message' => 'Attendance session locked successfully.',
            'data' => new StudentAttendanceSessionResource($studentSession),
        ]);
    }
}
