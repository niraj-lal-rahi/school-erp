<?php

namespace App\Services\Transport;

use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteStop;
use App\Models\Transport\TransportRouteVehicleAssignment;
use App\Repositories\Contracts\Transport\TransportRouteRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportRouteStopRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportRouteVehicleAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportRouteService
{
    public function __construct(
        protected TransportRouteRepositoryInterface $routes,
        protected TransportRouteStopRepositoryInterface $stops,
        protected TransportRouteVehicleAssignmentRepositoryInterface $assignments,
    ) {
    }

    public function paginateRoutes(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->routes->paginate($filters, $perPage);
    }

    public function showRoute(TransportRoute $route): TransportRoute
    {
        return $this->routes->findOrFail($route->id);
    }

    public function createRoute(array $attributes): TransportRoute
    {
        return DB::transaction(fn (): TransportRoute => $this->routes->create($attributes));
    }

    public function updateRoute(TransportRoute $route, array $attributes): TransportRoute
    {
        return DB::transaction(fn (): TransportRoute => $this->routes->update($route, $attributes));
    }

    public function deleteRoute(TransportRoute $route): void
    {
        DB::transaction(function () use ($route): void {
            $this->routes->delete($route);
        });
    }

    public function paginateStops(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->stops->paginate($filters, $perPage);
    }

    public function stopsForRoute(TransportRoute $route): Collection
    {
        return $this->stops->forRoute($route->id);
    }

    public function createStop(array $attributes): TransportRouteStop
    {
        return DB::transaction(function () use ($attributes): TransportRouteStop {
            $this->guardRouteScopedStop($attributes);

            return $this->stops->create($attributes);
        });
    }

    public function updateStop(TransportRouteStop $routeStop, array $attributes): TransportRouteStop
    {
        return DB::transaction(function () use ($routeStop, $attributes): TransportRouteStop {
            $payload = array_merge($routeStop->only([
                'route_id',
                'name',
                'code',
                'stop_order',
                'pickup_time',
                'drop_time',
                'latitude',
                'longitude',
                'distance_from_start_km',
                'address',
                'status',
            ]), $attributes);

            $this->guardRouteScopedStop($payload, $routeStop->id);

            return $this->stops->update($routeStop, $attributes);
        });
    }

    public function deleteStop(TransportRouteStop $routeStop): void
    {
        DB::transaction(function () use ($routeStop): void {
            $this->stops->delete($routeStop);
        });
    }

    public function paginateAssignments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->assignments->paginate($filters, $perPage);
    }

    public function showAssignment(TransportRouteVehicleAssignment $assignment): TransportRouteVehicleAssignment
    {
        return $this->assignments->findOrFail($assignment->id);
    }

    public function createAssignment(array $attributes): TransportRouteVehicleAssignment
    {
        return DB::transaction(function () use ($attributes): TransportRouteVehicleAssignment {
            $this->guardAssignmentConflicts($attributes);

            return $this->assignments->create($attributes);
        });
    }

    public function updateAssignment(
        TransportRouteVehicleAssignment $assignment,
        array $attributes,
    ): TransportRouteVehicleAssignment {
        return DB::transaction(function () use ($assignment, $attributes): TransportRouteVehicleAssignment {
            $payload = array_merge($assignment->only([
                'route_id',
                'vehicle_id',
                'driver_id',
                'academic_year_id',
                'assigned_from',
                'assigned_to',
                'shift_type',
                'status',
                'notes',
            ]), $attributes);

            $this->guardAssignmentConflicts($payload, $assignment->id);

            return $this->assignments->update($assignment, $attributes);
        });
    }

    public function deleteAssignment(TransportRouteVehicleAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment): void {
            $this->assignments->delete($assignment);
        });
    }

    protected function guardRouteScopedStop(array $attributes, ?int $ignoreStopId = null): void
    {
        $routeId = (int) $attributes['route_id'];
        $stopOrder = (int) $attributes['stop_order'];
        $code = $attributes['code'] ?? null;

        $route = TransportRoute::query()->findOrFail($routeId);

        $duplicateOrder = TransportRouteStop::query()
            ->where('school_id', $route->school_id)
            ->where('route_id', $routeId)
            ->where('stop_order', $stopOrder)
            ->when($ignoreStopId, fn ($query) => $query->where('id', '!=', $ignoreStopId))
            ->exists();

        if ($duplicateOrder) {
            throw ValidationException::withMessages([
                'stop_order' => ['Stop order already exists for this route.'],
            ]);
        }

        if ($code) {
            $duplicateCode = TransportRouteStop::query()
                ->where('school_id', $route->school_id)
                ->where('code', $code)
                ->when($ignoreStopId, fn ($query) => $query->where('id', '!=', $ignoreStopId))
                ->exists();

            if ($duplicateCode) {
                throw ValidationException::withMessages([
                    'code' => ['Stop code already exists for this tenant.'],
                ]);
            }
        }
    }

    protected function guardAssignmentConflicts(array $attributes, ?int $ignoreId = null): void
    {
        $assignmentFrom = $attributes['assigned_from'];
        $assignmentTo = $attributes['assigned_to'] ?? $assignmentFrom;

        $vehicleConflict = TransportRouteVehicleAssignment::query()
            ->where('school_id', $attributes['school_id'])
            ->where('vehicle_id', $attributes['vehicle_id'])
            ->where('status', 'active')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereDate('assigned_from', '<=', $assignmentTo)
            ->where(function ($query) use ($assignmentFrom): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', $assignmentFrom);
            })
            ->exists();

        if ($vehicleConflict) {
            throw ValidationException::withMessages([
                'vehicle_id' => ['This vehicle already has an overlapping active route assignment.'],
            ]);
        }

        if (! empty($attributes['driver_id'])) {
            $driverConflict = TransportRouteVehicleAssignment::query()
                ->where('school_id', $attributes['school_id'])
                ->where('driver_id', $attributes['driver_id'])
                ->where('status', 'active')
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->whereDate('assigned_from', '<=', $assignmentTo)
                ->where(function ($query) use ($assignmentFrom): void {
                    $query->whereNull('assigned_to')
                        ->orWhereDate('assigned_to', '>=', $assignmentFrom);
                })
                ->exists();

            if ($driverConflict) {
                throw ValidationException::withMessages([
                    'driver_id' => ['This driver already has an overlapping active route assignment.'],
                ]);
            }
        }
    }
}
