<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\BiometricLogData;
use App\Models\Attendance\BiometricLog;
use Illuminate\Database\Eloquent\Collection;

interface BiometricLogRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(BiometricLogData $data): BiometricLog;

    public function update(BiometricLog $biometricLog, BiometricLogData $data): BiometricLog;

    public function pending(array $filters = []): Collection;
}
