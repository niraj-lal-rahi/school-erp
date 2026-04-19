<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentHouseData;
use App\Models\StudentHouse;
use Illuminate\Database\Eloquent\Collection;

interface StudentHouseRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(StudentHouseData $data): StudentHouse;

    public function update(StudentHouse $house, StudentHouseData $data): StudentHouse;

    public function delete(StudentHouse $house): void;
}
