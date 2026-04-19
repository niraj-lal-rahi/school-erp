<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentHouseData;
use App\Models\StudentHouse;
use App\Repositories\Contracts\StudentHouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentHouseRepository implements StudentHouseRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StudentHouse::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($houseQuery) use ($search): void {
                    $houseQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->withCount('students')
            ->orderBy('name')
            ->get();
    }

    public function create(StudentHouseData $data): StudentHouse
    {
        return StudentHouse::create($data->attributes);
    }

    public function update(StudentHouse $house, StudentHouseData $data): StudentHouse
    {
        $house->update($data->attributes);

        return $house->refresh()->loadCount('students');
    }

    public function delete(StudentHouse $house): void
    {
        $house->delete();
    }
}
