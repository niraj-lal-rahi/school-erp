<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Http\Resources\HR\LeaveBalanceResource;
use App\Models\HR\Staff;
use App\Services\HR\LeaveBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function __construct(protected LeaveBalanceService $balances)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => LeaveBalanceResource::collection(
                $this->balances->all($request->only(['staff_id', 'leave_type_id', 'academic_year_id']))
            ),
        ]);
    }

    public function staffBalance(Staff $staff, Request $request): JsonResponse
    {
        return response()->json([
            'data' => LeaveBalanceResource::collection(
                $this->balances->all([
                    ...$request->only(['leave_type_id', 'academic_year_id']),
                    'staff_id' => $staff->id,
                ])
            ),
        ]);
    }
}
