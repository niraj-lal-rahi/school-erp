<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\UpdateDashboardLayoutRequest;
use App\Services\Reports\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard,
    ) {
    }

    public function overview(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboard->overview([
                ...$request->only(['date', 'date_from', 'date_to', 'academic_year_id', 'class_id', 'section_id', 'module']),
                'school_id' => $request->user()->school_id,
            ]),
        ]);
    }

    public function widgets(Request $request): JsonResponse
    {
        $userType = (string) $request->input('user_type', 'admin');
        $userId = (int) $request->user()->id;

        return response()->json([
            'data' => $this->dashboard->widgets(
                $userType,
                $userId,
                [
                    ...$request->only(['module', 'status', 'date', 'date_from', 'date_to', 'academic_year_id', 'class_id', 'section_id']),
                    'school_id' => $request->user()->school_id,
                ],
            ),
        ]);
    }

    public function updateLayout(UpdateDashboardLayoutRequest $request): JsonResponse
    {
        $userType = (string) $request->input('user_type', 'admin');
        $layout = $this->dashboard->updateLayout(
            $userType,
            (int) $request->user()->id,
            $request->validated()['layout'],
            (int) $request->user()->school_id,
        );

        return response()->json([
            'message' => 'Dashboard layout updated successfully.',
            'data' => $layout,
        ]);
    }
}
