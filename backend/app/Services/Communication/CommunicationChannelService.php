<?php

namespace App\Services\Communication;

use App\Models\Communication\CommunicationChannel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommunicationChannelService
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CommunicationChannel::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($channelQuery) use ($search): void {
                    $channelQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $value) => $query->where('status', $value))
            ->when($filters['channel_type'] ?? null, fn ($query, string $value) => $query->where('channel_type', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): CommunicationChannel
    {
        return CommunicationChannel::query()->findOrFail($id);
    }

    public function create(array $attributes): CommunicationChannel
    {
        return DB::transaction(fn (): CommunicationChannel => CommunicationChannel::query()->create($attributes));
    }

    public function update(CommunicationChannel $channel, array $attributes): CommunicationChannel
    {
        return DB::transaction(function () use ($channel, $attributes): CommunicationChannel {
            $channel->update($attributes);

            return $channel->refresh();
        });
    }

    public function delete(CommunicationChannel $channel): void
    {
        DB::transaction(function () use ($channel): void {
            $channel->delete();
        });
    }
}
