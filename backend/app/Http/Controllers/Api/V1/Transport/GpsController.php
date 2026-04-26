<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\StoreTransportGpsLogRequest;
use App\Models\Transport\TransportGpsLog;
use App\Models\Transport\TransportVehicle;
use App\Services\Transport\TransportGpsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    public function __construct(
        protected TransportGpsService $gpsLogs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->gpsLogs->paginate(
                $request->only(['vehicle_id', 'transport_trip_id', 'driver_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(StoreTransportGpsLogRequest $request): JsonResponse
    {
        $gpsLog = $this->gpsLogs->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'GPS log created successfully.',
            'data' => $gpsLog,
        ], 201);
    }

    public function show(TransportGpsLog $gps): JsonResponse
    {
        return response()->json([
            'data' => $this->gpsLogs->show($gps),
        ]);
    }

    public function vehicleLocation(TransportVehicle $vehicle): JsonResponse
    {
        return response()->json([
            'data' => $this->gpsLogs->latestVehicleLocation($vehicle->id),
        ]);
    }
}
