<?php

namespace App\Http\Controllers\Api\V1\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Resources\Timetable\TimetablePeriodResource;
use App\Http\Resources\Timetable\TimetableRoomResource;
use App\Http\Resources\Timetable\TimetableVersionResource;
use App\Models\AcademicYear;
use App\Models\Attendance\AttendancePeriod;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\HR\Staff;
use App\Models\AcademicManagement\Subject;
use App\Models\Timetable\TimetableRoom;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimetableOptionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasPermission('timetable.view'), 403);

        return response()->json([
            'data' => [
                'academic_years' => AcademicYear::query()->orderByDesc('start_date')->get(['id', 'name', 'code']),
                'periods' => TimetablePeriodResource::collection(AttendancePeriod::query()->orderBy('sequence')->get()),
                'rooms' => TimetableRoomResource::collection(TimetableRoom::query()->orderBy('name')->get()),
                'versions' => TimetableVersionResource::collection(TimetableVersion::query()->with(['academicYear', 'creator', 'publishLogs'])->orderByDesc('effective_from')->get()),
                'classes' => SchoolClass::query()->orderBy('name')->get(['id', 'name', 'code']),
                'sections' => Section::query()->orderBy('name')->get(['id', 'name', 'code', 'school_class_id']),
                'staff' => Staff::query()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
                'subjects' => Subject::query()->orderBy('name')->get(['id', 'name', 'code']),
            ],
        ]);
    }
}
