<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\CommunicationMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommunicationMessageRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): CommunicationMessage;

    public function create(array $attributes): CommunicationMessage;

    public function update(CommunicationMessage $message, array $attributes): CommunicationMessage;

    public function delete(CommunicationMessage $message): void;
}
