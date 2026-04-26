<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertVehicleFuelLogRequest;
use App\Models\Transport\VehicleFuelLog;
use App\Services\Transport\VehicleFuelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    public function __construct(
        protected VehicleFuelService $fuelLogs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->fuelLogs->paginate(
                $request->only(['vehicle_id', 'date_from', 'date_to', 'search']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertVehicleFuelLogRequest $request): JsonResponse
    {
        $fuelLog = $this->fuelLogs->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'recorded_by' => $request->validated('recorded_by', $request->user()->id),
        ]);

        return response()->json([
            'message' => 'Fuel log created successfully.',
            'data' => $fuelLog,
        ], 201);
    }

    public function show(VehicleFuelLog $fuel): JsonResponse
    {
        return response()->json([
            'data' => $this->fuelLogs->show($fuel),
        ]);
    }

    public function update(UpsertVehicleFuelLogRequest $request, VehicleFuelLog $fuel): JsonResponse
    {
        $fuel = $this->fuelLogs->update($fuel, $request->validated());

        return response()->json([
            'message' => 'Fuel log updated successfully.',
            'data' => $fuel,
        ]);
    }

    public function destroy(VehicleFuelLog $fuel): JsonResponse
    {
        $this->fuelLogs->delete($fuel);

        return response()->json(null, 204);
    }
}
