<?php

namespace App\Repositories\Eloquent\Communication;

use App\Models\Communication\Announcement;
use App\Repositories\Contracts\Communication\AnnouncementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementRepository implements AnnouncementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $announcementQuery) use ($search): void {
                    $announcementQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['audience_type'] ?? null, fn (Builder $query, string $value) => $query->where('audience_type', $value))
            ->when($filters['announcement_type'] ?? null, fn (Builder $query, string $value) => $query->where('announcement_type', $value))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $value) => $query->where('priority', $value))
            ->when($filters['class_id'] ?? null, fn (Builder $query, $value) => $query->where('class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, $value) => $query->where('section_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('publish_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('publish_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Announcement
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): Announcement
    {
        $announcement = Announcement::create($attributes);

        return $this->findOrFail($announcement->id);
    }

    public function update(Announcement $announcement, array $attributes): Announcement
    {
        $announcement->update($attributes);

        return $this->findOrFail($announcement->id);
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }

    protected function query(): Builder
    {
        return Announcement::query()
            ->with([
                'academicYear',
                'schoolClass',
                'section',
                'creator',
                'publisher',
            ])
            ->withCount([
                'recipients',
                'attachments',
            ]);
    }
}
