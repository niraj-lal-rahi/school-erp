<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentHouseData;
use App\Models\StudentHouse;
use App\Repositories\Contracts\StudentHouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentHouseService
{
    public function __construct(
        protected StudentHouseRepositoryInterface $houses,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->houses->all($filters);
    }

    public function create(StudentHouseData $data): StudentHouse
    {
        return DB::transaction(fn (): StudentHouse => $this->houses->create($data));
    }

    public function update(StudentHouse $house, StudentHouseData $data): StudentHouse
    {
        return DB::transaction(fn (): StudentHouse => $this->houses->update($house, $data));
    }

    public function delete(StudentHouse $house): void
    {
        DB::transaction(fn (): bool => $house->delete());
    }
}
