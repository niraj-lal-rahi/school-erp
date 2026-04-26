<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\MessageTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageTemplateRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): MessageTemplate;

    public function create(array $attributes): MessageTemplate;

    public function update(MessageTemplate $template, array $attributes): MessageTemplate;

    public function delete(MessageTemplate $template): void;
}
