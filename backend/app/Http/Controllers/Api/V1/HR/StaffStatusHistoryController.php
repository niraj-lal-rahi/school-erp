<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Http\Resources\HR\StaffStatusHistoryResource;
use App\Models\HR\Staff;
use App\Services\HR\StaffLifecycleService;
use Illuminate\Http\JsonResponse;

class StaffStatusHistoryController extends Controller
{
    public function __construct(protected StaffLifecycleService $lifecycle)
    {
    }

    public function staffHistory(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffStatusHistoryResource::collection($this->lifecycle->historyForStaff($staff)),
        ]);
    }
}
