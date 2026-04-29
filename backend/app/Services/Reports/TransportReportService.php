<?php

namespace App\Services\Reports;

use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportTrip;
use App\Models\Transport\TransportVehicle;
use Illuminate\Database\Eloquent\Builder;

class TransportReportService
{
    public function routeWiseStudents(array $filters = []): array
    {
        $rows = $this->routeQuery($filters)
            ->withCount([
                'studentAllocations as active_student_allocations_count' => fn (Builder $query) => $query->where('status', 'active'),
                'staffAllocations as active_staff_allocations_count' => fn (Builder $query) => $query->where('status', 'active'),
            ])
            ->get()
            ->map(fn (TransportRoute $route) => [
                'route_id' => $route->id,
                'route_name' => $route->name,
                'route_code' => $route->code,
                'student_count' => (int) $route->active_student_allocations_count,
                'staff_count' => (int) $route->active_staff_allocations_count,
                'total_allocations' => (int) $route->active_student_allocations_count + (int) $route->active_staff_allocations_count,
            ])
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function vehicleUtilization(array $filters = []): array
    {
        $rows = TransportVehicle::query()
            ->withCount([
                'trips as trips_count' => function (Builder $query) use ($filters): void {
                    $query
                        ->when($filters['date_from'] ?? null, fn (Builder $nested, string $value) => $nested->whereDate('trip_date', '>=', $value))
                        ->when($filters['date_to'] ?? null, fn (Builder $nested, string $value) => $nested->whereDate('trip_date', '<=', $value))
                        ->when($filters['route_id'] ?? null, fn (Builder $nested, int|string $value) => $nested->where('route_id', $value));
                },
            ])
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->get()
            ->map(function (TransportVehicle $vehicle): array {
                $completedTrips = $vehicle->trips()->where('status', 'completed')->get();
                $avgBoarded = $completedTrips->count() > 0 ? round($completedTrips->avg('total_boarded') ?? 0, 2) : 0.0;
                $capacity = (int) ($vehicle->capacity ?? 0);

                return [
                    'vehicle_id' => $vehicle->id,
                    'vehicle_no' => $vehicle->vehicle_no,
                    'registration_no' => $vehicle->registration_no,
                    'capacity' => $capacity,
                    'trips_count' => (int) $vehicle->trips_count,
                    'average_boarded' => $avgBoarded,
                    'utilization_percentage' => $capacity > 0 ? round(($avgBoarded / $capacity) * 100, 2) : 0.0,
                ];
            })
            ->values()
            ->all();

        return ['rows' => $rows];
    }

    public function overview(array $filters = []): array
    {
        $studentAllocations = StudentTransportAllocation::query()->where('status', 'active');
        $staffAllocations = StaffTransportAllocation::query()->where('status', 'active');
        $todayTrips = TransportTrip::query()
            ->whereDate('trip_date', $filters['date'] ?? now()->toDateString());

        return [
            'summary' => [
                'active_routes' => $this->routeQuery($filters)->where('status', 'active')->count(),
                'active_student_allocations' => $studentAllocations->count(),
                'active_staff_allocations' => $staffAllocations->count(),
                'today_trips' => $todayTrips->count(),
                'completed_today_trips' => (clone $todayTrips)->where('status', 'completed')->count(),
            ],
            'route_rows' => $this->routeWiseStudents($filters)['rows'],
            'vehicle_rows' => $this->vehicleUtilization($filters)['rows'],
        ];
    }

    protected function routeQuery(array $filters = []): Builder
    {
        return TransportRoute::query()
            ->when($filters['route_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value));
    }
}
