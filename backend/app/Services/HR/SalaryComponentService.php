<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\SalaryComponentData;
use App\Models\HR\SalaryComponent;
use App\Repositories\Contracts\HR\SalaryComponentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalaryComponentService
{
    public function __construct(
        protected SalaryComponentRepositoryInterface $components,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->components->all($filters);
    }

    public function create(SalaryComponentData $data): SalaryComponent
    {
        return DB::transaction(fn (): SalaryComponent => $this->components->create($data));
    }

    public function update(SalaryComponent $component, SalaryComponentData $data): SalaryComponent
    {
        return DB::transaction(fn (): SalaryComponent => $this->components->update($component, $data));
    }

    public function delete(SalaryComponent $component): void
    {
        DB::transaction(fn (): bool => $component->delete());
    }
}
