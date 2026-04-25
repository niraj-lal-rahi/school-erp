<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\DataTransferObjects\Attendance\BiometricLogData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreBiometricLogRequest;
use App\Http\Resources\Attendance\BiometricLogResource;
use App\Models\Attendance\BiometricLog;
use App\Services\Attendance\BiometricLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiometricLogController extends Controller
{
    public function __construct(
        protected BiometricLogService $biometricLogs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BiometricLog::class);

        return response()->json([
            'data' => BiometricLogResource::collection(
                $this->biometricLogs->all($request->only(['user_type', 'user_id', 'processed', 'date_from', 'date_to']))
            ),
        ]);
    }

    public function store(StoreBiometricLogRequest $request): JsonResponse
    {
        $biometricLog = $this->biometricLogs->create(BiometricLogData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'processed' => false,
        ]));

        return response()->json([
            'message' => 'Biometric log created successfully.',
            'data' => new BiometricLogResource($biometricLog),
        ], 201);
    }

    public function show(BiometricLog $biometricLog): JsonResponse
    {
        $this->authorize('view', $biometricLog);

        return response()->json([
            'data' => new BiometricLogResource($biometricLog),
        ]);
    }

    public function process(Request $request): JsonResponse
    {
        $this->authorize('create', BiometricLog::class);

        $filters = $request->only(['user_type']);
        $this->biometricLogs->queueProcess($request->user()->school_id, $filters);

        return response()->json([
            'message' => 'Biometric log processing started.',
        ]);
    }
}
