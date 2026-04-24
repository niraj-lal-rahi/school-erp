<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\PayrollRunData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertPayrollRunRequest;
use App\Http\Resources\HR\PayrollRunResource;
use App\Models\HR\PayrollRun;
use App\Services\HR\PayrollRunService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollRunController extends Controller
{
    public function __construct(protected PayrollRunService $runs)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => PayrollRunResource::collection($this->runs->all($request->only(['payroll_month', 'payroll_year', 'status']))),
        ]);
    }

    public function store(UpsertPayrollRunRequest $request): JsonResponse
    {
        $run = $this->runs->create(PayrollRunData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'status' => $request->validated('status') ?? 'draft',
        ]));

        return response()->json([
            'message' => 'Payroll run created successfully.',
            'data' => new PayrollRunResource($run),
        ], 201);
    }

    public function show(PayrollRun $payrollRun): JsonResponse
    {
        return response()->json([
            'data' => new PayrollRunResource($this->runs->show($payrollRun)),
        ]);
    }

    public function update(UpsertPayrollRunRequest $request, PayrollRun $payrollRun): JsonResponse
    {
        $run = $this->runs->update($payrollRun, PayrollRunData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Payroll run updated successfully.',
            'data' => new PayrollRunResource($run),
        ]);
    }

    public function destroy(PayrollRun $payrollRun): JsonResponse
    {
        $this->runs->delete($payrollRun);

        return response()->json(null, 204);
    }

    public function process(PayrollRun $payrollRun, Request $request): JsonResponse
    {
        $run = $this->runs->process($payrollRun, $request->user()->id);

        return response()->json([
            'message' => 'Payroll run processed successfully.',
            'data' => new PayrollRunResource($run),
        ]);
    }

    public function finalize(PayrollRun $payrollRun, Request $request): JsonResponse
    {
        $run = $this->runs->finalize($payrollRun, $request->user()->id);

        return response()->json([
            'message' => 'Payroll run finalized successfully.',
            'data' => new PayrollRunResource($run),
        ]);
    }

    public function markPaid(PayrollRun $payrollRun, Request $request): JsonResponse
    {
        $run = $this->runs->markPaid($payrollRun, $request->user()->id);

        return response()->json([
            'message' => 'Payroll run marked as paid successfully.',
            'data' => new PayrollRunResource($run),
        ]);
    }
}
