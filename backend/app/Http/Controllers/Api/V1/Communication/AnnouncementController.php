<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\PublishAnnouncementRequest;
use App\Http\Requests\Communication\StoreAnnouncementRequest;
use App\Http\Requests\Communication\UpdateAnnouncementRequest;
use App\Models\Communication\Announcement;
use App\Services\Communication\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AnnouncementService $announcements,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->announcements->paginate(
                $request->only(['search', 'status', 'audience_type', 'announcement_type', 'priority', 'class_id', 'section_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $announcement = $this->announcements->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Announcement created successfully.',
            'data' => $announcement,
        ], 201);
    }

    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json([
            'data' => $this->announcements->findOrFail($announcement->id),
        ]);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $announcement = $this->announcements->update($announcement, [
            ...$request->validated(),
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Announcement updated successfully.',
            'data' => $announcement,
        ]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->announcements->delete($announcement);

        return response()->json(null, 204);
    }

    public function publish(PublishAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $announcement = $this->announcements->publish($announcement, $request->user()->id, $request->validated());

        return response()->json([
            'message' => 'Announcement published successfully.',
            'data' => $announcement,
        ]);
    }

    public function cancel(Announcement $announcement): JsonResponse
    {
        $announcement = $this->announcements->cancel($announcement);

        return response()->json([
            'message' => 'Announcement cancelled successfully.',
            'data' => $announcement,
        ]);
    }

    public function recipients(Announcement $announcement): JsonResponse
    {
        return response()->json([
            'data' => $this->announcements->recipientsForAnnouncement($announcement),
        ]);
    }
}
