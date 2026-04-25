<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\DataTransferObjects\Timetable\TimetableRoomData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Timetable\UpsertTimetableRoomRequest;
use App\Http\Resources\Timetable\TimetableRoomResource;
use App\Models\Timetable\TimetableRoom;
use App\Services\Timetable\TimetableRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableRoomController extends Controller
{
    public function __construct(
        protected TimetableRoomService $rooms,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TimetableRoom::class);

        return response()->json([
            'data' => TimetableRoomResource::collection(
                $this->rooms->all($request->only(['search', 'room_type', 'status']))
            ),
        ]);
    }

    public function store(UpsertTimetableRoomRequest $request): JsonResponse
    {
        $this->authorize('create', TimetableRoom::class);

        $room = $this->rooms->create(TimetableRoomData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable room created successfully.',
            'data' => new TimetableRoomResource($room),
        ], 201);
    }

    public function show(TimetableRoom $room): JsonResponse
    {
        $this->authorize('view', $room);

        return response()->json([
            'data' => new TimetableRoomResource($room),
        ]);
    }

    public function update(UpsertTimetableRoomRequest $request, TimetableRoom $room): JsonResponse
    {
        $this->authorize('update', $room);

        $room = $this->rooms->update($room, TimetableRoomData::fromArray([
            ...$request->validated(),
            'school_id' => $room->school_id,
        ]));

        return response()->json([
            'message' => 'Timetable room updated successfully.',
            'data' => new TimetableRoomResource($room),
        ]);
    }

    public function destroy(TimetableRoom $room): JsonResponse
    {
        $this->authorize('delete', $room);
        $this->rooms->delete($room);

        return response()->json(null, 204);
    }
}
