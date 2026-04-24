<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\SalaryComponentData;
use App\Models\HR\SalaryComponent;
use Illuminate\Database\Eloquent\Collection;

interface SalaryComponentRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(SalaryComponentData $data): SalaryComponent;

    public function update(SalaryComponent $component, SalaryComponentData $data): SalaryComponent;

    public function delete(SalaryComponent $component): void;
}
