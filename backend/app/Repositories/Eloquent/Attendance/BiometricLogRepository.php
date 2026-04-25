<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\BiometricLogData;
use App\Models\Attendance\BiometricLog;
use App\Repositories\Contracts\Attendance\BiometricLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BiometricLogRepository implements BiometricLogRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return BiometricLog::query()
            ->when($filters['user_type'] ?? null, fn ($query, string $type) => $query->where('user_type', $type))
            ->when($filters['user_id'] ?? null, fn ($query, int|string $id) => $query->where('user_id', $id))
            ->when(isset($filters['processed']), fn ($query, $processed) => $query->where('processed', filter_var($processed, FILTER_VALIDATE_BOOL)))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->where('log_datetime', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->where('log_datetime', '<=', $date))
            ->orderByDesc('log_datetime')
            ->orderByDesc('id')
            ->get();
    }

    public function create(BiometricLogData $data): BiometricLog
    {
        return BiometricLog::create($data->attributes);
    }

    public function update(BiometricLog $biometricLog, BiometricLogData $data): BiometricLog
    {
        $biometricLog->update($data->attributes);

        return $biometricLog->refresh();
    }

    public function pending(array $filters = []): Collection
    {
        return BiometricLog::query()
            ->where('processed', false)
            ->when($filters['user_type'] ?? null, fn ($query, string $type) => $query->where('user_type', $type))
            ->orderBy('log_datetime')
            ->get();
    }
}
