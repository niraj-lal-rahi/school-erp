<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceRecordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\UpdateStudentAttendanceRecordRequest;
use App\Http\Resources\Attendance\StudentAttendanceRecordResource;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Services\Attendance\StudentAttendanceRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceRecordController extends Controller
{
    public function __construct(
        protected StudentAttendanceRecordService $records,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', StudentAttendanceRecord::class);

        return response()->json([
            'data' => StudentAttendanceRecordResource::collection(
                $this->records->all($request->only([
                    'student_id',
                    'attendance_session_id',
                    'attendance_status_type_id',
                    'attendance_date_from',
                    'attendance_date_to',
                ]))
            ),
        ]);
    }

    public function show(StudentAttendanceRecord $studentRecord): JsonResponse
    {
        $this->authorize('view', $studentRecord);

        return response()->json([
            'data' => new StudentAttendanceRecordResource($studentRecord->load(['student', 'attendanceStatus', 'marker', 'session'])),
        ]);
    }

    public function update(UpdateStudentAttendanceRecordRequest $request, StudentAttendanceRecord $studentRecord): JsonResponse
    {
        $this->authorize('update', $studentRecord);

        $studentRecord = $this->records->update($studentRecord, StudentAttendanceRecordData::fromArray([
            ...$request->validated(),
            'school_id' => $studentRecord->school_id,
            'attendance_session_id' => $studentRecord->attendance_session_id,
            'student_id' => $studentRecord->student_id,
            'marked_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Attendance record updated successfully.',
            'data' => new StudentAttendanceRecordResource($studentRecord),
        ]);
    }

    public function destroy(StudentAttendanceRecord $studentRecord): JsonResponse
    {
        $this->authorize('delete', $studentRecord);
        $this->records->delete($studentRecord);

        return response()->json(null, 204);
    }
}
