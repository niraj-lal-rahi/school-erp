<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertTransportDriverRequest;
use App\Models\Transport\TransportDriver;
use App\Services\Transport\TransportDriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(
        protected TransportDriverService $drivers,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->drivers->paginate(
                $request->only(['search', 'status', 'staff_id']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertTransportDriverRequest $request): JsonResponse
    {
        $driver = $this->drivers->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Driver created successfully.',
            'data' => $driver,
        ], 201);
    }

    public function show(TransportDriver $driver): JsonResponse
    {
        return response()->json([
            'data' => $this->drivers->show($driver),
        ]);
    }

    public function update(UpsertTransportDriverRequest $request, TransportDriver $driver): JsonResponse
    {
        $driver = $this->drivers->update($driver, $request->validated());

        return response()->json([
            'message' => 'Driver updated successfully.',
            'data' => $driver,
        ]);
    }

    public function destroy(TransportDriver $driver): JsonResponse
    {
        $this->drivers->delete($driver);

        return response()->json(null, 204);
    }
}
