<?php

namespace App\Services\Communication;

use App\Models\Communication\MessageTemplate;
use App\Repositories\Contracts\Communication\MessageTemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MessageTemplateService
{
    public function __construct(
        protected MessageTemplateRepositoryInterface $templates,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->templates->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): MessageTemplate
    {
        return $this->templates->findOrFail($id);
    }

    public function create(array $attributes): MessageTemplate
    {
        return DB::transaction(fn (): MessageTemplate => $this->templates->create($attributes));
    }

    public function update(MessageTemplate $template, array $attributes): MessageTemplate
    {
        return DB::transaction(fn (): MessageTemplate => $this->templates->update($template, $attributes));
    }

    public function delete(MessageTemplate $template): void
    {
        DB::transaction(function () use ($template): void {
            $this->templates->delete($template);
        });
    }
}
