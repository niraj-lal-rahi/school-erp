<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\GuardianData;
use App\Models\Guardian;
use Illuminate\Database\Eloquent\Collection;

interface GuardianRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(GuardianData $data): Guardian;

    public function update(Guardian $guardian, GuardianData $data): Guardian;

    public function delete(Guardian $guardian): void;
}
