<?php

namespace App\Repositories\Contracts\Finance;

use App\DataTransferObjects\Finance\FeeStructureData;
use App\Models\Finance\FeeStructure;
use Illuminate\Database\Eloquent\Collection;

interface FeeStructureRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(FeeStructureData $data): FeeStructure;

    public function update(FeeStructure $feeStructure, FeeStructureData $data): FeeStructure;

    public function delete(FeeStructure $feeStructure): void;
}
