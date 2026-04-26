<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\MessageTemplate;
use App\Repositories\Contracts\Communication\MessageTemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MessageTemplateRepository implements MessageTemplateRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $templateQuery) use ($search): void {
                    $templateQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['channel'] ?? null, fn (Builder $query, string $value) => $query->where('template_type', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): MessageTemplate
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): MessageTemplate
    {
        $template = MessageTemplate::create($attributes);

        return $this->findOrFail($template->id);
    }

    public function update(MessageTemplate $template, array $attributes): MessageTemplate
    {
        $template->update($attributes);

        return $this->findOrFail($template->id);
    }

    public function delete(MessageTemplate $template): void
    {
        $template->delete();
    }

    protected function query(): Builder
    {
        return MessageTemplate::query()->withCount([
            'notificationLogs',
            'scheduledMessages',
        ]);
    }
}
