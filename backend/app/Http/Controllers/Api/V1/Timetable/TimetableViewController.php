<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Timetable\TimetableEntryResource;
use App\Models\Timetable\TimetableEntry;
use App\Services\Timetable\TimetableEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableViewController extends Controller
{
    public function __construct(
        protected TimetableEntryService $entries,
    ) {
    }

    public function classWeekly(Request $request, int $classId, int $sectionId): JsonResponse
    {
        $this->authorize('viewAny', TimetableEntry::class);

        return response()->json([
            'data' => TimetableEntryResource::collection(
                $this->entries->weeklyForClassSection(
                    $request->user()->school_id,
                    $classId,
                    $sectionId,
                    $request->only(['academic_year_id', 'timetable_version_id'])
                )
            ),
        ]);
    }

    public function staffWeekly(Request $request, int $staffId): JsonResponse
    {
        $this->authorize('viewAny', TimetableEntry::class);

        return response()->json([
            'data' => TimetableEntryResource::collection(
                $this->entries->weeklyForStaff(
                    $request->user()->school_id,
                    $staffId,
                    $request->only(['academic_year_id', 'timetable_version_id'])
                )
            ),
        ]);
    }

    public function staffDaily(Request $request, int $staffId): JsonResponse
    {
        $this->authorize('viewAny', TimetableEntry::class);

        return response()->json([
            'data' => TimetableEntryResource::collection(
                $this->entries->dailyForStaff(
                    $request->user()->school_id,
                    $staffId,
                    strtolower((string) $request->query('day_of_week', now()->englishDayOfWeek)),
                    $request->only(['academic_year_id', 'timetable_version_id'])
                )
            ),
        ]);
    }
}
