<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpdatePayslipRequest;
use App\Http\Resources\HR\StaffPayslipResource;
use App\Models\HR\StaffPayslip;
use App\Services\HR\StaffPayslipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffPayslipController extends Controller
{
    public function __construct(protected StaffPayslipService $payslips)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => StaffPayslipResource::collection($this->payslips->all($request->only(['payroll_run_id', 'staff_id', 'payment_status']))),
        ]);
    }

    public function show(StaffPayslip $payslip): JsonResponse
    {
        return response()->json([
            'data' => new StaffPayslipResource($this->payslips->show($payslip)),
        ]);
    }

    public function update(UpdatePayslipRequest $request, StaffPayslip $payslip): JsonResponse
    {
        $updated = $this->payslips->update($payslip, $request->validated());

        return response()->json([
            'message' => 'Payslip updated successfully.',
            'data' => new StaffPayslipResource($updated),
        ]);
    }
}
