<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\UpdateNotificationPreferenceRequest;
use App\Models\Communication\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $preferences = NotificationPreference::query()
            ->where('school_id', $request->user()->school_id)
            ->when($request->filled('user_type'), fn ($query) => $query->where('user_type', $request->string('user_type')->toString()))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->latest('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'data' => $preferences,
        ]);
    }

    public function show(NotificationPreference $preference): JsonResponse
    {
        return response()->json([
            'data' => $preference,
        ]);
    }

    public function update(UpdateNotificationPreferenceRequest $request, NotificationPreference $preference): JsonResponse
    {
        $preference->update($request->validated());

        return response()->json([
            'message' => 'Notification preference updated successfully.',
            'data' => $preference->refresh(),
        ]);
    }
}
