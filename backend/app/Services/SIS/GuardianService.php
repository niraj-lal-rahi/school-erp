<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\GuardianData;
use App\Models\Guardian;
use App\Repositories\Contracts\GuardianRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GuardianService
{
    public function __construct(
        protected GuardianRepositoryInterface $guardians,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->guardians->all($filters);
    }

    public function create(GuardianData $data): Guardian
    {
        return DB::transaction(fn (): Guardian => $this->guardians->create($data));
    }

    public function update(Guardian $guardian, GuardianData $data): Guardian
    {
        return DB::transaction(fn (): Guardian => $this->guardians->update($guardian, $data));
    }

    public function delete(Guardian $guardian): void
    {
        DB::transaction(function () use ($guardian): void {
            $this->guardians->delete($guardian);
        });
    }
}
