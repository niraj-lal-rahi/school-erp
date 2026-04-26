<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertVehicleMaintenanceLogRequest;
use App\Models\Transport\VehicleMaintenanceLog;
use App\Services\Transport\VehicleMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(
        protected VehicleMaintenanceService $maintenance,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->maintenance->paginate(
                $request->only(['vehicle_id', 'maintenance_type', 'status', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertVehicleMaintenanceLogRequest $request): JsonResponse
    {
        $maintenanceLog = $this->maintenance->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'recorded_by' => $request->validated('recorded_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Maintenance log created successfully.',
            'data' => $maintenanceLog,
        ], 201);
    }

    public function show(VehicleMaintenanceLog $maintenance): JsonResponse
    {
        return response()->json([
            'data' => $this->maintenance->show($maintenance),
        ]);
    }

    public function update(
        UpsertVehicleMaintenanceLogRequest $request,
        VehicleMaintenanceLog $maintenance,
    ): JsonResponse {
        $maintenance = $this->maintenance->update($maintenance, $request->validated());

        return response()->json([
            'message' => 'Maintenance log updated successfully.',
            'data' => $maintenance,
        ]);
    }

    public function destroy(VehicleMaintenanceLog $maintenance): JsonResponse
    {
        $this->maintenance->delete($maintenance);

        return response()->json(null, 204);
    }
}
