<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertTransportRouteRequest;
use App\Http\Requests\Transport\UpsertTransportRouteVehicleAssignmentRequest;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteVehicleAssignment;
use App\Services\Transport\TransportRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function __construct(
        protected TransportRouteService $routes,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->paginateRoutes(
                $request->only(['search', 'route_type', 'status']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function store(UpsertTransportRouteRequest $request): JsonResponse
    {
        $route = $this->routes->createRoute([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Route created successfully.',
            'data' => $route,
        ], 201);
    }

    public function show(TransportRoute $route): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->showRoute($route),
        ]);
    }

    public function update(UpsertTransportRouteRequest $request, TransportRoute $route): JsonResponse
    {
        $route = $this->routes->updateRoute($route, $request->validated());

        return response()->json([
            'message' => 'Route updated successfully.',
            'data' => $route,
        ]);
    }

    public function destroy(TransportRoute $route): JsonResponse
    {
        $this->routes->deleteRoute($route);

        return response()->json(null, 204);
    }

    public function assignmentIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->paginateAssignments(
                $request->only(['route_id', 'vehicle_id', 'driver_id', 'academic_year_id', 'status']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function assignmentStore(UpsertTransportRouteVehicleAssignmentRequest $request): JsonResponse
    {
        $assignment = $this->routes->createAssignment([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Route vehicle assignment created successfully.',
            'data' => $assignment,
        ], 201);
    }

    public function assignmentShow(TransportRouteVehicleAssignment $assignment): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->showAssignment($assignment),
        ]);
    }

    public function assignmentUpdate(
        UpsertTransportRouteVehicleAssignmentRequest $request,
        TransportRouteVehicleAssignment $assignment,
    ): JsonResponse {
        $assignment = $this->routes->updateAssignment($assignment, $request->validated());

        return response()->json([
            'message' => 'Route vehicle assignment updated successfully.',
            'data' => $assignment,
        ]);
    }

    public function assignmentDestroy(TransportRouteVehicleAssignment $assignment): JsonResponse
    {
        $this->routes->deleteAssignment($assignment);

        return response()->json(null, 204);
    }
}
