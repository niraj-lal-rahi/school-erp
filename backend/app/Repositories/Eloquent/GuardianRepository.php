<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\GuardianData;
use App\Models\Guardian;
use App\Repositories\Contracts\GuardianRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GuardianRepository implements GuardianRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return Guardian::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($guardianQuery) use ($search): void {
                    $guardianQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('alternate_phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->get();
    }

    public function create(GuardianData $data): Guardian
    {
        return Guardian::create($data->attributes);
    }

    public function update(Guardian $guardian, GuardianData $data): Guardian
    {
        $guardian->update($data->attributes);

        return $guardian->refresh();
    }

    public function delete(Guardian $guardian): void
    {
        $guardian->delete();
    }
}
