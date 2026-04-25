<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetableRoomData;
use App\Models\Timetable\TimetableRoom;
use App\Repositories\Contracts\Timetable\TimetableRoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TimetableRoomService
{
    public function __construct(
        protected TimetableRoomRepositoryInterface $rooms,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->rooms->all($filters);
    }

    public function create(TimetableRoomData $data): TimetableRoom
    {
        return DB::transaction(fn (): TimetableRoom => $this->rooms->create($data));
    }

    public function update(TimetableRoom $room, TimetableRoomData $data): TimetableRoom
    {
        return DB::transaction(fn (): TimetableRoom => $this->rooms->update($room, $data));
    }

    public function delete(TimetableRoom $room): void
    {
        DB::transaction(function () use ($room): void {
            $this->rooms->delete($room);
        });
    }
}
