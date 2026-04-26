<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertTransportVehicleRequest;
use App\Models\Transport\TransportVehicle;
use App\Services\Transport\TransportVehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected TransportVehicleService $vehicles,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->vehicles->paginate(
                $request->only(['search', 'vehicle_type', 'status']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertTransportVehicleRequest $request): JsonResponse
    {
        $vehicle = $this->vehicles->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Vehicle created successfully.',
            'data' => $vehicle,
        ], 201);
    }

    public function show(TransportVehicle $vehicle): JsonResponse
    {
        return response()->json([
            'data' => $this->vehicles->show($vehicle),
        ]);
    }

    public function update(UpsertTransportVehicleRequest $request, TransportVehicle $vehicle): JsonResponse
    {
        $vehicle = $this->vehicles->update($vehicle, $request->validated());

        return response()->json([
            'message' => 'Vehicle updated successfully.',
            'data' => $vehicle,
        ]);
    }

    public function destroy(TransportVehicle $vehicle): JsonResponse
    {
        $this->vehicles->delete($vehicle);

        return response()->json(null, 204);
    }

    public function location(TransportVehicle $vehicle): JsonResponse
    {
        return response()->json([
            'data' => $this->vehicles->latestLocation($vehicle),
        ]);
    }
}
