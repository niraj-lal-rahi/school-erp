<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetableRoomData;
use App\Models\Timetable\TimetableRoom;
use App\Repositories\Contracts\Timetable\TimetableRoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetableRoomRepository implements TimetableRoomRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return TimetableRoom::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($roomQuery) use ($search): void {
                    $roomQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('building', 'like', "%{$search}%");
                });
            })
            ->when($filters['room_type'] ?? null, fn ($query, string $roomType) => $query->where('room_type', $roomType))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function create(TimetableRoomData $data): TimetableRoom
    {
        return TimetableRoom::create($data->attributes);
    }

    public function update(TimetableRoom $room, TimetableRoomData $data): TimetableRoom
    {
        $room->update($data->attributes);

        return $room->refresh();
    }

    public function delete(TimetableRoom $room): void
    {
        $room->delete();
    }
}
