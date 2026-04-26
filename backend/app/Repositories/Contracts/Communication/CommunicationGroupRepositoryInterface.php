<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\CommunicationGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommunicationGroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): CommunicationGroup;

    public function create(array $attributes): CommunicationGroup;

    public function update(CommunicationGroup $group, array $attributes): CommunicationGroup;

    public function delete(CommunicationGroup $group): void;
}
