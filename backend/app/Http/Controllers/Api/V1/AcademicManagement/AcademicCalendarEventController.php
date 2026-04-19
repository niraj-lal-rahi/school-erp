<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertAcademicCalendarEventRequest;
use App\Http\Resources\AcademicManagement\AcademicCalendarEventResource;
use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Services\AcademicManagement\AcademicCalendarEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicCalendarEventController extends Controller
{
    public function __construct(protected AcademicCalendarEventService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', AcademicCalendarEvent::class);

        return AcademicCalendarEventResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'school_class_id', 'section_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertAcademicCalendarEventRequest $request): JsonResponse
    {
        $this->authorize('create', AcademicCalendarEvent::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Academic calendar event created successfully.', 'data' => new AcademicCalendarEventResource($record)], 201);
    }

    public function show(AcademicCalendarEvent $academicCalendar): AcademicCalendarEventResource
    {
        $this->authorize('view', $academicCalendar);

        return new AcademicCalendarEventResource($this->service->show($academicCalendar->id));
    }

    public function update(UpsertAcademicCalendarEventRequest $request, AcademicCalendarEvent $academicCalendar): JsonResponse
    {
        $this->authorize('update', $academicCalendar);
        $updated = $this->service->update($academicCalendar, $request->validated());

        return response()->json(['message' => 'Academic calendar event updated successfully.', 'data' => new AcademicCalendarEventResource($updated)]);
    }

    public function destroy(AcademicCalendarEvent $academicCalendar): JsonResponse
    {
        $this->authorize('delete', $academicCalendar);
        $this->service->delete($academicCalendar);

        return response()->json(null, 204);
    }
}
