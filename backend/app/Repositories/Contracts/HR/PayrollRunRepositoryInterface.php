<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\PayrollRunData;
use App\Models\HR\PayrollRun;
use Illuminate\Database\Eloquent\Collection;

interface PayrollRunRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): PayrollRun;

    public function create(PayrollRunData $data): PayrollRun;

    public function update(PayrollRun $run, PayrollRunData $data): PayrollRun;

    public function delete(PayrollRun $run): void;
}
