<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\AttendanceImportData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceImportRequest;
use App\Http\Resources\Attendance\AttendanceImportResource;
use App\Models\Attendance\AttendanceImport;
use App\Services\Attendance\AttendanceImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceImportController extends Controller
{
    public function __construct(
        protected AttendanceImportService $imports,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceImport::class);

        return response()->json([
            'data' => AttendanceImportResource::collection(
                $this->imports->all($request->only(['import_type', 'status', 'date_from', 'date_to']))
            ),
        ]);
    }

    public function store(StoreAttendanceImportRequest $request): JsonResponse
    {
        $attendanceImport = $this->imports->create(AttendanceImportData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'uploaded_by' => $request->user()->id,
        ]));

        return response()->json([
            'message' => 'Attendance import created successfully.',
            'data' => new AttendanceImportResource($attendanceImport),
        ], 201);
    }

    public function show(AttendanceImport $attendanceImport): JsonResponse
    {
        $this->authorize('view', $attendanceImport);

        return response()->json([
            'data' => new AttendanceImportResource($attendanceImport->load('uploader')),
        ]);
    }

    public function process(AttendanceImport $attendanceImport): JsonResponse
    {
        $this->authorize('update', $attendanceImport);

        $attendanceImport = $this->imports->queueProcess($attendanceImport);

        return response()->json([
            'message' => 'Attendance import processing started.',
            'data' => new AttendanceImportResource($attendanceImport),
        ]);
    }
}
