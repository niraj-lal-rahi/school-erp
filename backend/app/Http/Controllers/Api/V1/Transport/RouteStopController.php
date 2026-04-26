<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertTransportRouteStopRequest;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteStop;
use App\Services\Transport\TransportRouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteStopController extends Controller
{
    public function __construct(
        protected TransportRouteService $routes,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->paginateStops(
                $request->only(['route_id', 'status', 'search']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function routeStops(TransportRoute $route): JsonResponse
    {
        return response()->json([
            'data' => $this->routes->stopsForRoute($route),
        ]);
    }

    public function store(UpsertTransportRouteStopRequest $request): JsonResponse
    {
        $routeStop = $this->routes->createStop([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Route stop created successfully.',
            'data' => $routeStop,
        ], 201);
    }

    public function show(TransportRouteStop $routeStop): JsonResponse
    {
        return response()->json([
            'data' => $routeStop->load('route'),
        ]);
    }

    public function update(UpsertTransportRouteStopRequest $request, TransportRouteStop $routeStop): JsonResponse
    {
        $routeStop = $this->routes->updateStop($routeStop, $request->validated());

        return response()->json([
            'message' => 'Route stop updated successfully.',
            'data' => $routeStop,
        ]);
    }

    public function destroy(TransportRouteStop $routeStop): JsonResponse
    {
        $this->routes->deleteStop($routeStop);

        return response()->json(null, 204);
    }
}
