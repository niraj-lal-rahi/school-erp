<?php

namespace App\Services\Transport;

use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportTrip;
use App\Models\Transport\TransportTripLog;
use App\Repositories\Contracts\Transport\TransportTripLogRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportTripRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportRouteVehicleAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportTripService
{
    public function __construct(
        protected TransportTripRepositoryInterface $trips,
        protected TransportTripLogRepositoryInterface $tripLogs,
        protected TransportRouteVehicleAssignmentRepositoryInterface $routeAssignments,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->trips->paginate($filters, $perPage);
    }

    public function show(TransportTrip $trip): TransportTrip
    {
        return $this->trips->findOrFail($trip->id);
    }

    public function create(array $attributes): TransportTrip
    {
        return DB::transaction(function () use ($attributes): TransportTrip {
            $this->guardTrip($attributes);

            return $this->trips->create($attributes);
        });
    }

    public function update(TransportTrip $trip, array $attributes): TransportTrip
    {
        return DB::transaction(function () use ($trip, $attributes): TransportTrip {
            $payload = array_merge($trip->only([
                'school_id',
                'route_vehicle_assignment_id',
                'route_id',
                'vehicle_id',
                'driver_id',
                'trip_date',
                'trip_type',
                'scheduled_start_time',
                'scheduled_end_time',
                'status',
                'notes',
            ]), $attributes);

            $this->guardTrip($payload, $trip->id);

            return $this->trips->update($trip, $attributes);
        });
    }

    public function delete(TransportTrip $trip): void
    {
        DB::transaction(function () use ($trip): void {
            $this->trips->delete($trip);
        });
    }

    public function start(TransportTrip $trip, array $attributes = []): TransportTrip
    {
        return DB::transaction(function () use ($trip, $attributes): TransportTrip {
            if ($trip->status !== 'scheduled') {
                throw ValidationException::withMessages([
                    'status' => ['Only scheduled trips can be started.'],
                ]);
            }

            return $this->trips->update($trip, [
                'status' => 'in_progress',
                'started_at' => $attributes['started_at'] ?? now(),
                'started_by' => $attributes['started_by'] ?? null,
                'notes' => $attributes['notes'] ?? $trip->notes,
            ]);
        });
    }

    public function complete(TransportTrip $trip, array $attributes = []): TransportTrip
    {
        return DB::transaction(function () use ($trip, $attributes): TransportTrip {
            if (! in_array($trip->status, ['scheduled', 'in_progress'], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Only scheduled or in-progress trips can be completed.'],
                ]);
            }

            return $this->trips->update($trip, [
                'status' => 'completed',
                'completed_at' => $attributes['completed_at'] ?? now(),
                'completed_by' => $attributes['completed_by'] ?? null,
                'notes' => $attributes['notes'] ?? $trip->notes,
            ]);
        });
    }

    public function cancel(TransportTrip $trip, string $reason): TransportTrip
    {
        return DB::transaction(function () use ($trip, $reason): TransportTrip {
            if ($trip->status === 'completed') {
                throw ValidationException::withMessages([
                    'status' => ['Completed trips cannot be cancelled.'],
                ]);
            }

            return $this->trips->update($trip, [
                'status' => 'cancelled',
                'notes' => trim(($trip->notes ? $trip->notes.PHP_EOL : '').'Cancellation reason: '.$reason),
            ]);
        });
    }

    public function paginateLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tripLogs->paginate($filters, $perPage);
    }

    public function markBoarded(TransportTrip $trip, array $attributes): TransportTripLog
    {
        return $this->recordTripEvent($trip, array_merge($attributes, ['event_type' => 'boarded']));
    }

    public function markDropped(TransportTrip $trip, array $attributes): TransportTripLog
    {
        return $this->recordTripEvent($trip, array_merge($attributes, ['event_type' => 'dropped']));
    }

    public function recordTripEvent(TransportTrip $trip, array $attributes): TransportTripLog
    {
        return DB::transaction(function () use ($trip, $attributes): TransportTripLog {
            if (! in_array($trip->status, ['scheduled', 'in_progress', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'transport_trip_id' => ['Trip event logging is not allowed for the current trip state.'],
                ]);
            }

            $payload = array_merge($attributes, [
                'school_id' => $trip->school_id,
                'transport_trip_id' => $trip->id,
            ]);

            $this->guardTripLog($trip, $payload);

            $log = $this->tripLogs->create($payload);
            $this->recalculateTripCounters($trip);

            return $log;
        });
    }

    protected function guardTrip(array $attributes, ?int $ignoreId = null): void
    {
        $assignment = $this->routeAssignments->findOrFail((int) $attributes['route_vehicle_assignment_id']);

        if ((int) $assignment->route_id !== (int) $attributes['route_id']) {
            throw ValidationException::withMessages([
                'route_id' => ['Trip route must match the selected vehicle assignment route.'],
            ]);
        }

        if ((int) $assignment->vehicle_id !== (int) $attributes['vehicle_id']) {
            throw ValidationException::withMessages([
                'vehicle_id' => ['Trip vehicle must match the selected vehicle assignment.'],
            ]);
        }

        if (! empty($attributes['driver_id']) && (int) $assignment->driver_id !== (int) $attributes['driver_id']) {
            throw ValidationException::withMessages([
                'driver_id' => ['Trip driver must match the selected route assignment driver.'],
            ]);
        }

        $existing = $this->trips->currentForAssignmentOnDate(
            (int) $attributes['route_vehicle_assignment_id'],
            (string) $attributes['trip_date'],
            (string) $attributes['trip_type'],
            $ignoreId,
        );

        if ($existing) {
            throw ValidationException::withMessages([
                'route_vehicle_assignment_id' => ['A scheduled or active trip already exists for this assignment, date, and trip type.'],
            ]);
        }
    }

    protected function guardTripLog(TransportTrip $trip, array $attributes): void
    {
        if (($attributes['user_type'] ?? null) === 'student') {
            $allocation = StudentTransportAllocation::query()
                ->where('school_id', $trip->school_id)
                ->where('student_id', $attributes['student_id'] ?? null)
                ->where('route_id', $trip->route_id)
                ->where('status', 'active')
                ->first();

            if (! $allocation) {
                throw ValidationException::withMessages([
                    'student_id' => ['The selected student does not have an active allocation on this route.'],
                ]);
            }
        }

        if (($attributes['user_type'] ?? null) === 'staff') {
            $allocation = StaffTransportAllocation::query()
                ->where('school_id', $trip->school_id)
                ->where('staff_id', $attributes['staff_id'] ?? null)
                ->where('route_id', $trip->route_id)
                ->where('status', 'active')
                ->first();

            if (! $allocation) {
                throw ValidationException::withMessages([
                    'staff_id' => ['The selected staff member does not have an active allocation on this route.'],
                ]);
            }
        }
    }

    protected function recalculateTripCounters(TransportTrip $trip): void
    {
        $freshTrip = $this->trips->findOrFail($trip->id);

        $this->trips->update($freshTrip, [
            'total_boarded' => $freshTrip->tripLogs()->where('event_type', 'boarded')->count(),
            'total_dropped' => $freshTrip->tripLogs()->where('event_type', 'dropped')->count(),
        ]);
    }
}
