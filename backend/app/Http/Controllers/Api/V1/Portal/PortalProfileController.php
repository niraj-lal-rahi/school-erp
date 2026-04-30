<?php

namespace App\Http\Controllers\Api\V1\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\LinkPortalProfileRequest;
use App\Http\Requests\Portal\UpdatePortalAccessRequest;
use App\Http\Resources\Portal\PortalProfileResource;
use App\Models\Guardian;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Portal\PortalUserProfile;
use App\Models\Student;
use App\Models\User;
use App\Services\Portal\PortalProfileService;
use Illuminate\Http\JsonResponse;

class PortalProfileController extends Controller
{
    public function __construct(
        protected PortalProfileService $profileService,
    ) {
    }

    public function linkStudent(LinkPortalProfileRequest $request): JsonResponse
    {
        $this->authorize('create', PortalUserProfile::class);

        $user = User::query()->findOrFail($request->integer('user_id'));
        $student = Student::query()->findOrFail($request->integer('student_id'));

        $profile = $this->profileService->linkStudentProfile($user, $student, $request->validated());

        return response()->json([
            'message' => 'Student portal profile linked successfully.',
            'data' => new PortalProfileResource($profile),
        ]);
    }

    public function linkGuardian(LinkPortalProfileRequest $request): JsonResponse
    {
        $this->authorize('create', PortalUserProfile::class);

        $user = User::query()->findOrFail($request->integer('user_id'));
        $guardian = Guardian::query()->findOrFail($request->integer('guardian_id'));

        $profile = $this->profileService->linkGuardianProfile($user, $guardian, $request->validated());

        return response()->json([
            'message' => 'Guardian portal profile linked successfully.',
            'data' => new PortalProfileResource($profile),
        ]);
    }

    public function updateAccess(UpdatePortalAccessRequest $request, int $id): JsonResponse
    {
        $access = PortalProfileAccess::query()->findOrFail($id);
        $this->authorize('update', $access);
        $updated = $this->profileService->updateAccess($access, $request->validated());

        return response()->json([
            'message' => 'Portal access updated successfully.',
            'data' => $updated,
        ]);
    }
}
