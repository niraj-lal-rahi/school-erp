<?php

namespace App\Repositories\Contracts\Communication;

use App\Models\Communication\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AnnouncementRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Announcement;

    public function create(array $attributes): Announcement;

    public function update(Announcement $announcement, array $attributes): Announcement;

    public function delete(Announcement $announcement): void;
}
