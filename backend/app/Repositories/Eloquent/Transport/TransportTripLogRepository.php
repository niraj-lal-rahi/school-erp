<?php

namespace App\Repositories\Eloquent\Transport;

use App\Models\Transport\TransportTripLog;
use App\Repositories\Contracts\Transport\TransportTripLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TransportTripLogRepository implements TransportTripLogRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['transport_trip_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('transport_trip_id', $value))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['staff_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('staff_id', $value))
            ->when($filters['event_type'] ?? null, fn (Builder $query, string $value) => $query->where('event_type', $value))
            ->when($filters['user_type'] ?? null, fn (Builder $query, string $value) => $query->where('user_type', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->where('event_time', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->where('event_time', '<=', $value))
            ->latest('event_time')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): TransportTripLog
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): TransportTripLog
    {
        $tripLog = TransportTripLog::create($attributes);

        return $this->findOrFail($tripLog->id);
    }

    public function update(TransportTripLog $tripLog, array $attributes): TransportTripLog
    {
        $tripLog->update($attributes);

        return $this->findOrFail($tripLog->id);
    }

    public function delete(TransportTripLog $tripLog): void
    {
        $tripLog->delete();
    }

    protected function query(): Builder
    {
        return TransportTripLog::query()->with(['trip', 'student', 'staff', 'routeStop', 'marker']);
    }
}
