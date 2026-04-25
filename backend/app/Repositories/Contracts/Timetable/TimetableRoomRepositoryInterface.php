<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetableRoomData;
use App\Models\Timetable\TimetableRoom;
use Illuminate\Database\Eloquent\Collection;

interface TimetableRoomRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetableRoomData $data): TimetableRoom;

    public function update(TimetableRoom $room, TimetableRoomData $data): TimetableRoom;

    public function delete(TimetableRoom $room): void;
}
